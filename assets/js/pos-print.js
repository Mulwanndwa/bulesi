/**
 * POS receipt printer — QZ Tray (ESC/POS) with browser-print fallback.
 *
 * QZ Tray must be installed on the cashier machine:
 *   https://qz.io/download/
 *
 * Usage:
 *   printReceipt({ quoteNumber, customer, notes, subtotal, vatAmount, total, createdAt, companyName, companyLogo }, items)
 *   items: [{ name, unit, quantity, unit_price, line_total }, …]
 */

const PosPrint = (() => {

    // ── ESC/POS command constants ──────────────────────────────────────
    const ESC = '\x1B';
    const GS  = '\x1D';
    const LF  = '\x0A';

    const CMD = {
        INIT        : ESC + '@',
        CUT         : GS  + 'V' + '\x41' + '\x03',   // partial cut + feed
        FEED3       : ESC + 'd' + '\x03',
        ALIGN_LEFT  : ESC + 'a' + '\x00',
        ALIGN_CENTER: ESC + 'a' + '\x01',
        BOLD_ON     : ESC + 'E' + '\x01',
        BOLD_OFF    : ESC + 'E' + '\x00',
        DOUBLE_ON   : ESC + '!' + '\x30',             // double width + height
        DOUBLE_OFF  : ESC + '!' + '\x00',
    };

    // Pad / truncate a string to exactly `len` chars
    function pad(str, len, right = false) {
        str = String(str ?? '').substring(0, len);
        return right ? str.padStart(len) : str.padEnd(len);
    }

    const fmt = n => 'R ' + parseFloat(n).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    const DIVIDER = '-'.repeat(42) + LF;

    // ── Build ESC/POS byte array ───────────────────────────────────────
    function buildEscPos(order, items) {
        const dt   = new Date(order.createdAt || Date.now());
        const date = dt.toLocaleDateString('en-ZA', { day: '2-digit', month: 'short', year: 'numeric' });
        const time = dt.toLocaleTimeString('en-ZA', { hour: '2-digit', minute: '2-digit' });

        const companyName = (order.companyName || 'BULESI').toUpperCase();

        let d = '';
        d += CMD.INIT;
        d += CMD.ALIGN_CENTER;
        d += CMD.BOLD_ON + CMD.DOUBLE_ON + companyName + LF + CMD.DOUBLE_OFF + CMD.BOLD_OFF;
        d += 'Point of Sale' + LF;
        d += CMD.ALIGN_LEFT;
        d += DIVIDER;
        d += 'No: ' + order.quoteNumber + LF;
        d += 'Date: ' + date + ' ' + time + LF;
        d += 'Customer: ' + (order.customer || 'Walk-in Customer') + LF;
        if (order.notes) d += 'Notes: ' + order.notes + LF;
        d += DIVIDER;

        // Header
        d += CMD.BOLD_ON;
        d += pad('ITEM', 24) + pad('QTY', 6, true) + pad('TOTAL', 12, true) + LF;
        d += CMD.BOLD_OFF;

        items.forEach(item => {
            const name  = pad(item.name, 24);
            const qty   = pad('x' + item.quantity, 6, true);
            const total = pad(fmt(item.line_total), 12, true);
            d += name + qty + total + LF;
            // Price per unit on second line if name is long
            d += '  ' + fmt(item.unit_price) + ' / ' + (item.unit || 'each') + LF;
        });

        d += DIVIDER;
        d += pad('Subtotal', 30) + pad(fmt(order.subtotal),  12, true) + LF;
        d += pad('VAT (15%)', 30) + pad(fmt(order.vatAmount), 12, true) + LF;
        d += CMD.BOLD_ON;
        d += pad('TOTAL', 30) + pad(fmt(order.total), 12, true) + LF;
        d += CMD.BOLD_OFF;
        d += DIVIDER;
        d += CMD.ALIGN_CENTER;
        d += 'Thank you for your purchase!' + LF;
        d += CMD.ALIGN_LEFT;
        d += LF;
        d += 'Customer Signature:' + LF;
        d += LF;
        d += '_'.repeat(38) + LF;
        d += LF;
        d += 'Date: ____________________________' + LF;
        d += CMD.FEED3;
        d += CMD.CUT;

        return d;
    }

    // ── Browser-print fallback (80mm HTML receipt) ─────────────────────
    function browserPrint(order, items) {
        const dt   = new Date(order.createdAt || Date.now());
        const date = dt.toLocaleDateString('en-ZA', { day: '2-digit', month: 'short', year: 'numeric' });
        const time = dt.toLocaleTimeString('en-ZA', { hour: '2-digit', minute: '2-digit' });

        const rows = items.map(i => {
            const name = i.name.length > 24 ? i.name.substring(0, 23) + '…' : i.name;
            return `<tr>
                <td>${name}</td>
                <td style="text-align:right">${i.quantity} x ${fmt(i.unit_price)}</td>
                <td style="text-align:right">${fmt(i.line_total)}</td>
            </tr>`;
        }).join('');

        const logoHtml = order.companyLogo
            ? `<img src="${order.companyLogo}" alt="" style="max-height:60px;max-width:120px;object-fit:contain;display:block;margin:0 auto 3px">`
            : '';
        const nameHtml = order.companyName
            ? `<div class="c b lg">${order.companyName}</div>`
            : '<div class="c b lg">BULESI</div>';

        const html = `<!DOCTYPE html><html><head><meta charset="UTF-8">
<title>Receipt ${order.quoteNumber}</title>
<style>
  @page { size: 80mm auto; margin: 4mm 3mm; }
  * { box-sizing: border-box; }
  body { font-family:'Courier New',Courier,monospace; font-size:11px; color:#000; margin:0; width:74mm; }
  .c { text-align:center; } .b { font-weight:bold; } .lg { font-size:14px; }
  hr { border:none; border-top:1px dashed #000; margin:4px 0; }
  table { width:100%; border-collapse:collapse; }
  th { text-align:left; font-weight:bold; border-bottom:1px solid #000; padding:2px 0; font-size:10px; text-transform:uppercase; }
  td { padding:2px 0; vertical-align:top; }
  td:last-child { text-align:right; white-space:nowrap; }
  .tot td { padding:1px 0; }
  .grand td { font-weight:bold; font-size:13px; border-top:1px solid #000; padding-top:3px; }
</style></head><body>
  ${logoHtml}${nameHtml}
  <div class="c" style="font-size:10px">Point of Sale</div><hr>
  <div>No: <b>${order.quoteNumber}</b></div>
  <div>Date: ${date} ${time}</div>
  <div>Customer: ${order.customer || 'Walk-in Customer'}</div>
  ${order.notes ? `<div>Notes: ${order.notes}</div>` : ''}<hr>
  <table>
    <thead><tr><th>Item</th><th style="text-align:right">Qty x Price</th><th style="text-align:right">Total</th></tr></thead>
    <tbody>${rows}</tbody>
  </table><hr>
  <table class="tot">
    <tr><td>Subtotal</td><td>${fmt(order.subtotal)}</td></tr>
    <tr><td>VAT (15%)</td><td>${fmt(order.vatAmount)}</td></tr>
    <tr class="grand"><td>TOTAL</td><td>${fmt(order.total)}</td></tr>
  </table><hr>
  <div class="c" style="font-size:10px;margin-top:4px">Thank you for your purchase!</div>
  <div style="margin-top:18px">
    <div style="font-size:10px;font-weight:bold;margin-bottom:2px">Customer Signature</div>
    <div style="border-top:1px solid #000;margin-top:28px;padding-top:3px;font-size:9px;color:#555">Signature</div>
    <div style="border-top:1px solid #000;margin-top:18px;padding-top:3px;font-size:9px;color:#555">Date</div>
  </div>
</body></html>`;

        const win = window.open('', '_blank', 'width=320,height=600,toolbar=0,menubar=0,scrollbars=1');
        if (!win) { alert('Pop-up blocked. Please allow pop-ups for this site.'); return; }
        win.document.write(html);
        win.document.close();
        win.focus();
        // Small delay ensures content is painted before print dialog
        setTimeout(() => { win.print(); win.close(); }, 400);
    }

    // null = untested, true = confirmed working, false = confirmed unavailable
    let qzAvailable = null;

    // ── QZ Tray connection + print ─────────────────────────────────────
    async function qzPrint(order, items) {
        qz.security.setCertificatePromise((resolve) => resolve());
        qz.security.setSignatureAlgorithm('SHA512');
        qz.security.setSignaturePromise((toSign) => (resolve) => resolve());

        if (!qz.websocket.isActive()) {
            // retries:0 + delay:0 makes it fail immediately instead of
            // hammering the WebSocket and flooding the console
            await qz.websocket.connect({ retries: 0, delay: 0 });
        }

        let printer;
        try {
            printer = await qz.printers.find('EPSON');
            if (Array.isArray(printer)) printer = printer[0];
        } catch (_) {
            printer = await qz.printers.getDefault();
        }

        const config = qz.configs.create(printer, { encoding: 'ISO-8859-1' });
        await qz.print(config, [{ type: 'raw', format: 'plain', data: buildEscPos(order, items) }]);
    }

    // ── Public API ─────────────────────────────────────────────────────
    async function printReceipt(order, items) {
        if (typeof qz !== 'undefined' && qzAvailable !== false) {
            try {
                await qzPrint(order, items);
                qzAvailable = true;
                return;
            } catch (err) {
                qzAvailable = false;
                console.info('QZ Tray not available — using browser print.');
            }
        }
        browserPrint(order, items);
    }

    return { printReceipt };
})();
