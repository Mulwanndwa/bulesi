/**
 * Push notification service — wraps cordova-plugin-firebasex.
 * Token registration uses the apiFetch helper passed in from home.vue
 * so it goes through the exact same fetch path as every other API call.
 */

const Push = {
  _ready:        false,
  _postFn:       null,   // apiFetch-compatible: (path, opts) => Promise
  _pendingToken: null,
  _setupDone:    false,  // true once _setup has issued its own getToken call

  _available() {
    return !!(window.cordova && window.FirebasePlugin);
  },

  async _sendToken(fcmToken) {
    if (!fcmToken || !this._postFn) return;
    if (!localStorage.getItem('qt_token')) return;   // not logged in

    try {
      await this._postFn('/push_token', {
        method: 'POST',
        body:   JSON.stringify({ push_token: fcmToken }),
      });
      this._pendingToken = null;
    } catch (err) {
      // Network wasn't ready (common at startup — FCM returns a cached token
      // before connectivity settles). Park the token so the next onLogin retries.
      this._pendingToken = fcmToken;
      console.warn('[Push] sendToken error:', err);
    }
  },

  // Called by home.vue after a successful login or session restore.
  onLogin() {
    if (this._pendingToken) {
      this._sendToken(this._pendingToken);
      return;
    }

    // _setup already issued getToken — don't fire a second concurrent request.
    if (this._setupDone) return;

    if (this._available() && this._ready) {
      FirebasePlugin.getToken(
        (token) => { if (token) this._sendToken(token); },
        (err)   => console.warn('[Push] onLogin getToken error:', err)
      );
    }
  },

  _setup(onMessage) {
    this._setupDone = true;
    FirebasePlugin.getToken(
      (token) => {
        if (!token) return;
        if (localStorage.getItem('qt_token')) {
          this._sendToken(token);
        } else {
          this._pendingToken = token;
        }
      },
      (err) => console.warn('[Push] getToken error:', err)
    );

    FirebasePlugin.onTokenRefresh(
      (token) => {
        if (!token) return;
        if (localStorage.getItem('qt_token')) {
          this._sendToken(token);
        } else {
          this._pendingToken = token;
        }
      },
      (err) => console.warn('[Push] onTokenRefresh error:', err)
    );

    FirebasePlugin.onMessageReceived(
      (message) => { if (onMessage) onMessage(message); },
      (err)     => console.warn('[Push] onMessageReceived error:', err)
    );
  },

  /**
   * @param {Function} postFn   The apiFetch helper from home.vue.
   * @param {Function} onMessage Called for every incoming message.
   *   message.tap === true  → user tapped a notification.
   *   message.quotation_id  → deep-link into that quotation.
   */
  init(postFn, onMessage) {
    if (!this._available() || this._ready) return;
    this._ready  = true;
    this._postFn = postFn;

    FirebasePlugin.hasPermission(
      (hasPermission) => {
        if (hasPermission) {
          this._setup(onMessage);
        } else {
          FirebasePlugin.grantPermission(
            (granted) => {
              if (granted) {
                this._setup(onMessage);
              } else {
                console.warn('[Push] Notification permission denied by user.');
              }
            },
            (err) => console.warn('[Push] grantPermission error:', err)
          );
        }
      },
      (err) => console.warn('[Push] hasPermission error:', err)
    );
  },
};

export default Push;
