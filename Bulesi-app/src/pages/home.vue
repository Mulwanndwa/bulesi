<template>
  <f7-page name="home">

    <!-- ── NAVBAR ────────────────────────────────────────────────────── -->
    <f7-navbar v-if="view !== 'login'">
      <f7-nav-left>
        <f7-link v-if="view !== homeView" @click="goBack" class="nav-back">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="15 18 9 12 15 6"/>
          </svg>
        </f7-link>
        <div v-else class="nav-brand">
          <span class="nav-brand-icon">
            <img src="../img/logo.png" alt="Bulise" style="width:18px;height:18px;object-fit:contain;" />
          </span>
          Bulise
        </div>
      </f7-nav-left>
      <f7-nav-right>
        <div class="nav-menu-wrap">
          <button class="nav-burger" @click.stop="menuOpen = !menuOpen">
            <span></span><span></span><span></span>
          </button>

          <!-- Teleport to body so the navbar stacking context cannot clip it -->
          <Teleport to="body">
            <div v-if="menuOpen" class="nav-menu-overlay" @click="menuOpen = false"></div>
            <div v-if="menuOpen" class="nav-dropdown" @click.stop>
            <!-- user pill -->
            <div class="nav-drop-user">
              <div class="user-avatar" style="width:32px;height:32px;flex-shrink:0">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                  <circle cx="12" cy="7" r="4"/>
                </svg>
              </div>
              <div>
                <div class="nav-drop-username">{{ user.username }}</div>
                <div class="nav-drop-group">{{ user.group_name }}</div>
              </div>
            </div>

            <div class="nav-drop-divider"></div>

            <!-- Refresh -->
            <button v-if="view === 'users'" class="nav-drop-item" @click="fetchUsers(); menuOpen = false">
              <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/>
                <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>
              </svg>
              Refresh
            </button>
            <button v-if="view === 'list'" class="nav-drop-item" @click="fetchQuotations(selectedUser ? selectedUser.id : null); menuOpen = false">
              <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/>
                <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>
              </svg>
              Refresh
            </button>

            <!-- Share -->
            <button v-if="view === 'detail'" class="nav-drop-item" @click="shareQuotation(); menuOpen = false">
              <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/>
                <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
              </svg>
              Share Quotation
            </button>

            <!-- Change Password -->
            <button v-if="view !== 'user-password'" class="nav-drop-item" @click="goToMyPassword(); menuOpen = false">
              <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
              </svg>
              Change Password
            </button>

            <div class="nav-drop-divider"></div>

            <!-- Sign Out -->
            <button class="nav-drop-item nav-drop-danger" @click="logout(); menuOpen = false">
              <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                <polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
              </svg>
              Sign Out
            </button>
          </div>
          </Teleport>
        </div>
      </f7-nav-right>
    </f7-navbar>

    <!-- ── BOTTOM TABBAR ─────────────────────────────────────────────── -->
    <!-- <f7-toolbar v-if="view !== 'login'" tabbar bottom icons>
      <f7-link @click="goToList" :class="{ 'tab-link-active': view === 'list' }">
        <i class="bi bi-files" style="font-size:1.1rem"></i>
        <span class="tabbar-label">Quotations</span>
      </f7-link>
      <f7-link @click="goToCreate" :class="{ 'tab-link-active': view === 'create' || view === 'success' }">
        <i class="bi bi-plus-circle" style="font-size:1.1rem"></i>
        <span class="tabbar-label">New Quote</span>
      </f7-link>
    </f7-toolbar> -->

    <!-- ── LOGIN ─────────────────────────────────────────────────────── -->
    <template v-if="view === 'login'">
      <div class="login-wrap">
        <div class="login-card">

          <div class="login-brand">
            <div class="login-logo"><img src="../img/logo.png" alt="Bulise" style="border-radius: 20px; width:108px;height:88px;object-fit:fill;" /></div>
            <!-- <div class="login-name">Bulise</div> -->
            <div class="login-tagline">Quotation management</div>
          </div>

          <div v-if="loginError" class="alert-err">
            <i class="bi bi-exclamation-circle-fill"></i>
            <span>{{ loginError }}</span>
          </div>

          <div class="lf-group">
            <label class="lf-label">Username</label>
            <div class="lf-input-wrap">
              <i class="bi bi-person lf-icon"></i>
              <input
                class="lf-input"
                type="text"
                placeholder="your username"
                :value="loginForm.login"
                @input="loginForm.login = $event.target.value"
                @keyup.enter="doLogin"
                autocomplete="username"
              />
            </div>
          </div>

          <div class="lf-group">
            <label class="lf-label">Password</label>
            <div class="lf-input-wrap">
              <i class="bi bi-lock lf-icon"></i>
              <input
                class="lf-input"
                :type="showPw ? 'text' : 'password'"
                placeholder="••••••••"
                :value="loginForm.password"
                @input="loginForm.password = $event.target.value"
                @keyup.enter="doLogin"
                autocomplete="current-password"
              />
              <button type="button" class="lf-pw-btn" @click="showPw = !showPw">
                <i :class="showPw ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
              </button>
            </div>
          </div>

          <button
            class="login-btn"
            @click="doLogin"
            :disabled="loginLoading || !loginForm.login || !loginForm.password"
          >
            <f7-preloader v-if="loginLoading" :size="18" color="white"></f7-preloader>
            <i v-else class="bi bi-box-arrow-in-right"></i>
            {{ loginLoading ? 'Signing in…' : 'Sign In' }}
          </button>

        </div>
      </div>
    </template>

    <!-- ── USERS (admin) ─────────────────────────────────────────────── -->
    <template v-if="view === 'users'">

      <div class="list-header">
        <div class="list-header-row">
          <div class="list-header-title">Staff</div>
          <span v-if="!usersLoading" class="list-count-pill">{{ filteredUsers.length }}</span>
        </div>
        <div class="search-bar-wrap">
          <i class="bi bi-search search-icon"></i>
          <input
            class="search-input"
            type="text"
            placeholder="Search by name or email…"
            :value="userSearch"
            @input="userSearch = $event.target.value"
          />
          <button v-if="userSearch" class="search-clear" @click="userSearch = ''">
            <i class="bi bi-x"></i>
          </button>
        </div>
      </div>

      <button class="list-fab" @click="goToUserCreate">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
      </button>

      <div v-if="usersLoading" class="list-spinner">
        <f7-preloader :size="36"></f7-preloader>
      </div>

      <div v-else-if="usersError" style="padding:16px">
        <div class="alert-err">
          <i class="bi bi-exclamation-circle-fill"></i>
          <span>{{ usersError }}</span>
        </div>
      </div>

      <div v-else-if="!filteredUsers.length" class="list-empty">
        <i class="bi bi-people" style="font-size:3rem;opacity:.3"></i>
        <p>No users found</p>
      </div>

      <div v-else class="qt-list">
        <div
          v-for="u in filteredUsers"
          :key="u.id"
          class="qt-card"
          @click="openUser(u)"
          style="cursor:pointer"
        >
          <div class="qt-card-main">
            <div class="user-avatar">
              <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
              </svg>
            </div>
            <div class="qt-card-left">
              <div class="qt-number">{{ u.username }}</div>
              <div class="qt-customer">{{ u.email }}</div>
            </div>
            <div class="qt-card-right">
              <span :class="['st-badge', u.is_active ? 'st-accepted' : 'st-cancelled']">
                {{ u.is_active ? 'active' : 'inactive' }}
              </span>
              <span v-if="u.group_name" class="st-badge" style="margin-top:4px;background:rgba(255,255,255,.1)">
                {{ u.group_name }}
              </span>
            </div>
          </div>
          <div class="qt-card-footer">
            <span><i class="bi bi-calendar3"></i> Joined {{ u.created_at?.slice(0,10) }}</span>
            <button class="qt-call-btn" @click.stop="goToChangePassword(u)">
              <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:3px">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
              </svg>
              Password
            </button>
          </div>
        </div>
      </div>

    </template>

    <!-- ── USER PASSWORD (admin) ─────────────────────────────────────── -->
    <template v-if="view === 'user-password'">

      <div class="page-hero">
        <div class="page-hero-inner">
          <div>
            <div class="page-hero-title">Change Password</div>
            <div class="page-hero-sub">{{ passwordUser?.username }}</div>
          </div>
          <div class="user-avatar" style="width:44px;height:44px;background:rgba(233,69,96,.25)">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#e94560" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
          </div>
        </div>
      </div>

      <div class="form-wrap" style="padding:24px 20px 60px">

        <div v-if="pwError" class="alert-err" style="margin-bottom:20px">
          <i class="bi bi-exclamation-circle-fill" style="flex-shrink:0;margin-top:2px"></i>
          <div>
            <strong>Could not update password</strong>
            <ul v-if="Array.isArray(pwError)" class="alert-list">
              <li v-for="e in pwError" :key="e">{{ e }}</li>
            </ul>
            <span v-else> — {{ pwError }}</span>
          </div>
        </div>

        <div v-if="pwSuccess" class="alert-ok" style="margin-bottom:20px">
          <i class="bi bi-check-circle-fill"></i>
          <span>Password updated successfully.</span>
        </div>

        <div class="lf-group">
          <label class="lf-label">New Password</label>
          <div class="lf-input-wrap">
            <i class="bi bi-lock lf-icon"></i>
            <input class="lf-input" :type="showNewPw ? 'text' : 'password'" placeholder="min. 6 characters"
              :value="pwForm.password" @input="pwForm.password = $event.target.value" autocomplete="new-password" />
            <button type="button" class="lf-pw-btn" @click="showNewPw = !showNewPw">
              <i :class="showNewPw ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
            </button>
          </div>
        </div>

        <div class="lf-group">
          <label class="lf-label">Confirm Password</label>
          <div class="lf-input-wrap">
            <i class="bi bi-lock-fill lf-icon"></i>
            <input class="lf-input" :type="showConfirmPw ? 'text' : 'password'" placeholder="repeat new password"
              :value="pwForm.confirm" @input="pwForm.confirm = $event.target.value" autocomplete="new-password" />
            <button type="button" class="lf-pw-btn" @click="showConfirmPw = !showConfirmPw">
              <i :class="showConfirmPw ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
            </button>
          </div>
        </div>

        <button class="login-btn" style="margin-top:24px" @click="submitPassword"
          :disabled="pwLoading || !pwForm.password || !pwForm.confirm">
          <f7-preloader v-if="pwLoading" :size="18" color="white"></f7-preloader>
          <i v-else class="bi bi-floppy"></i>
          {{ pwLoading ? 'Saving…' : 'Update Password' }}
        </button>

      </div>
    </template>

    <!-- ── USER CREATE (admin) ───────────────────────────────────────── -->
    <template v-if="view === 'user-create'">

      <div class="page-hero">
        <div class="page-hero-inner">
          <div>
            <div class="page-hero-title">Add Staff Member</div>
            <div class="page-hero-sub">Fill in the details below to create an account</div>
          </div>
          <div class="user-avatar" style="width:44px;height:44px;background:rgba(233,69,96,.25)">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#e94560" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
              <circle cx="12" cy="7" r="4"/>
            </svg>
          </div>
        </div>
      </div>

      <div class="form-wrap margin card" style="padding: 24px 20px 60px">

        <div v-if="userCreateError" class="alert-err" style="margin-bottom:20px">
          <i class="bi bi-exclamation-circle-fill" style="flex-shrink:0;margin-top:2px"></i>
          <div>
            <strong>Could not create user</strong>
            <ul v-if="Array.isArray(userCreateError)" class="alert-list">
              <li v-for="e in userCreateError" :key="e">{{ e }}</li>
            </ul>
            <span v-else> — {{ userCreateError }}</span>
          </div>
        </div>

        <div class="lf-group">
          <label class="lf-label">Username</label>
          <div class="lf-input-wrap">
            <i class="bi bi-person lf-icon"></i>
            <input class="lf-input" type="text" placeholder="e.g. john_doe"
              :value="userForm.username" @input="userForm.username = $event.target.value" autocomplete="off" />
          </div>
        </div>

        <div class="lf-group">
          <label class="lf-label">Email</label>
          <div class="lf-input-wrap">
            <i class="bi bi-envelope lf-icon"></i>
            <input class="lf-input" type="email" placeholder="staff@example.com"
              :value="userForm.email" @input="userForm.email = $event.target.value" autocomplete="off" />
          </div>
        </div>

        <div class="lf-group">
          <label class="lf-label">Password</label>
          <div class="lf-input-wrap">
            <i class="bi bi-lock lf-icon"></i>
            <input class="lf-input" :type="showUserPw ? 'text' : 'password'" placeholder="min. 6 characters"
              :value="userForm.password" @input="userForm.password = $event.target.value" autocomplete="new-password" />
            <button type="button" class="lf-pw-btn" @click="showUserPw = !showUserPw">
              <i :class="showUserPw ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
            </button>
          </div>
        </div>

        <div class="lf-group">
          <label class="lf-label">Role / Group</label>
          <div class="lf-input-wrap" style="padding-right:12px">
            <i class="bi bi-people lf-icon"></i>
            <select class="lf-input" style="cursor:pointer" :value="userForm.group_id"
              @change="userForm.group_id = parseInt($event.target.value)">
              <option value="" disabled>Select a role…</option>
              <option v-if="groupsLoading" disabled>Loading…</option>
              <option v-for="g in groups" :key="g.id" :value="g.id">{{ g.name }}</option>
            </select>
          </div>
        </div>

        <div class="lf-group" style="display:flex;align-items:center;justify-content:space-between;padding:4px 0">
          <label class="lf-label" style="margin:0">Active</label>
          <label class="uc-toggle">
            <input type="checkbox" :checked="userForm.is_active" @change="userForm.is_active = $event.target.checked" />
            <span class="uc-toggle-slider"></span>
          </label>
        </div>

        <button class="login-btn" style="margin-top:24px" @click="submitUser"
          :disabled="userCreateLoading || !userForm.username || !userForm.email || !userForm.password || !userForm.group_id">
          <f7-preloader v-if="userCreateLoading" :size="18" color="white"></f7-preloader>
          <svg v-else xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/>
          </svg>
          {{ userCreateLoading ? 'Creating…' : 'Create Staff Member' }}
        </button>

      </div>
    </template>

    <!-- ── LIST ─────────────────────────────────────────────────────── -->
    <template v-if="view === 'list'">

      <div class="list-header">
        <div class="list-header-row">
          <div class="list-header-title">
            {{ selectedUser ? selectedUser.username + "'s Quotations" : 'Quotations' }}
          </div>
          <span v-if="!quotationsLoading" class="list-count-pill">
            {{ filteredQuotations.length }}
          </span>
        </div>

        <!-- Search -->
        <div class="search-bar-wrap">
          <i class="bi bi-search search-icon"></i>
          <input
            class="search-input"
            type="text"
            placeholder="Search by customer, quote number…"
            :value="searchQuery"
            @input="searchQuery = $event.target.value"
          />
          <button v-if="searchQuery" class="search-clear" @click="searchQuery = ''">
            <i class="bi bi-x"></i>
          </button>
        </div>

        <!-- Date range -->
        <!-- <div class="date-range-row">
          <div class="date-input-wrap">
            <i class="bi bi-calendar3 date-icon"></i>
            <input class="date-input" type="date" :value="dateFrom" @input="dateFrom = $event.target.value" placeholder="From" />
          </div>
          <span class="date-sep">—</span>
          <div class="date-input-wrap">
            <i class="bi bi-calendar3 date-icon"></i>
            <input class="date-input" type="date" :value="dateTo" @input="dateTo = $event.target.value" placeholder="To" />
          </div>
          <button v-if="hasActiveFilters" class="clear-filters-btn" @click="clearFilters">
            <i class="bi bi-x-lg"></i> Clear
          </button>
        </div> -->

        <!-- Status chips -->
        <div class="filter-chips">
          <button
            v-for="s in statusFilters" :key="s.value"
            :class="['chip', listFilter === s.value ? 'active' : '']"
            @click="setListFilter(s.value)"
          >{{ s.label }}</button>
        </div>
      </div>

      <button class="list-fab" @click="goToCreate">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
      </button>

      <div v-if="quotationsLoading" class="list-spinner">
        <f7-preloader :size="36"></f7-preloader>
      </div>

      <div v-else-if="quotationsError" style="padding:16px">
        <div class="alert-err">
          <i class="bi bi-exclamation-circle-fill"></i>
          <span>{{ quotationsError }}</span>
        </div>
      </div>

      <div v-else-if="!filteredQuotations.length" class="list-empty">
        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="16" y1="13" x2="8" y2="13"/>
            <line x1="16" y1="17" x2="8" y2="17"/>
            <polyline points="10 9 9 9 8 9"/>
          </svg>
        <p>No quotations found</p>
      </div>

      <div v-else class="qt-list">
        <div v-for="q in filteredQuotations" :key="q.id" :class="['qt-card', 'qt-s-' + q.status]" @click="openQuotation(q)" style="cursor:pointer">
          <div class="qt-card-main">
            <div class="qt-card-left">
              <div class="qt-number">{{ q.quote_number }}</div>
              <div class="qt-customer">{{ q.customer_name }}</div>
            </div>
            <div class="qt-card-right">
              <div class="qt-total">R {{ fmt(q.total) }}</div>
              <span :class="['st-badge', 'st-' + q.status]">{{ q.status.replace('_', ' ') }}</span>
            </div>
          </div>
          <div class="qt-card-footer">
            <span><i class="bi bi-calendar3"></i> {{ q.quote_date }}</span>
            <span v-if="q.type_name"><i class="bi bi-tag"></i> {{ q.type_name }}</span>
            <span v-if="q.customer_email"><i class="bi bi-envelope"></i> {{ q.customer_email }}</span>
            <button v-if="q.customer_phone" class="qt-call-btn" @click.stop="callPhone(q.customer_phone)">
              <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.59 3.47 2 2 0 0 1 3.56 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.69a16 16 0 0 0 6.29 6.29l.86-.86a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>
              </svg>
              {{ q.customer_phone }}
            </button>
            <button class="qt-call-btn" @click.stop="goToEdit(q)">
              Edit
            </button>
          </div>
        </div>
      </div>

    </template>

    <!-- ── CREATE ────────────────────────────────────────────────────── -->
    <template v-if="view === 'create'">

      <div class="page-hero">
        <div class="page-hero-inner">
          <div>
            <div class="page-hero-title">{{ isEditing ? 'Edit Quotation' : 'New Quotation' }}</div>
            <div class="page-hero-sub">{{ isEditing ? selectedQuote.quote_number : 'Fill in the details below and submit' }}</div>
          </div>
          <span v-if="!isEditing" class="badge-draft">Draft</span>
          <span v-else :class="['st-badge', 'st-' + editStatus]">{{ editStatus.replace('_', ' ') }}</span>
        </div>
      </div>

      <div class="form-wrap">

        <div v-if="quoteError" class="alert-err" style="margin-bottom:16px">
          <i class="bi bi-exclamation-circle-fill" style="flex-shrink:0;margin-top:2px"></i>
          <div>
            <strong>{{ isEditing ? 'Could not update quotation' : 'Could not create quotation' }}</strong>
            <ul v-if="Array.isArray(quoteError)" class="alert-list">
              <li v-for="e in quoteError" :key="e">{{ e }}</li>
            </ul>
            <span v-else> — {{ quoteError }}</span>
          </div>
        </div>

        <!-- Customer -->
        <div class="section-head">Customer</div>
        <f7-list inset strong>
          <f7-list-input label="Customer Name *" type="text" placeholder="Acme Corp"
            :value="qForm.customer_name" @input="qForm.customer_name = $event.target.value" required validate>
            <template #media><i class="bi bi-building"></i></template>
          </f7-list-input>
          <f7-list-input label="Phone" type="text" placeholder="+27 82 123 4567"
            :value="qForm.customer_phone" @input="qForm.customer_phone = $event.target.value">
            <template #media><i class="bi bi-phone"></i></template>
          </f7-list-input>
          <f7-list-input label="Email" type="email" placeholder="client@example.com"
            :value="qForm.customer_email" @input="qForm.customer_email = $event.target.value">
            <template #media><i class="bi bi-envelope"></i></template>
          </f7-list-input>
          <f7-list-input label="Description" type="textarea" placeholder="Brief description of this quotation…"
            :value="qForm.description" @input="qForm.description = $event.target.value" resizable>
            <template #media><i class="bi bi-card-text"></i></template>
          </f7-list-input>
          <br/>
        </f7-list>

        <!-- Dates & VAT -->
        <div class="section-head">Dates &amp; Tax</div>
        <f7-list inset strong >
          <f7-list-input label="Quote Date *" type="date"
            :value="qForm.quote_date" @input="qForm.quote_date = $event.target.value" required validate>
            <template #media><i class="bi bi-calendar3"></i></template>
          </f7-list-input>
          <f7-list-input label="Valid Until" type="date"
            :value="qForm.valid_until" @input="qForm.valid_until = $event.target.value">
            <template #media><i class="bi bi-calendar-check"></i></template>
          </f7-list-input>
          <f7-list-input label="VAT Rate (%)" type="number" min="0" max="100" step="0.5"
            :value="qForm.vat_rate" @input="qForm.vat_rate = parseFloat($event.target.value) || 0" style="padding-bottom: 1em;">
            <template #media><i class="bi bi-percent"></i></template>
          </f7-list-input>
          <f7-list-input v-if="isEditing" label="Status" type="select"
            :value="editStatus" @change="editStatus = $event.target.value" style="padding-bottom: 1em;">
            <template #media><i class="bi bi-flag"></i></template>
            <option value="draft">Draft</option>
            <option value="sent">Sent</option>
            <option value="accepted">Accepted</option>
            <option value="in_progress">In Progress</option>
            <option value="completed">Completed</option>
            <option value="invoiced">Invoiced</option>
            <option value="rejected">Rejected</option>
            <option value="cancelled">Cancelled</option>
          </f7-list-input>
        
        </f7-list>

        <!-- Line Items -->
        <div class="section-head">Line Items</div>
        <div class="items-card">
          <div style="overflow-x:auto">
            <table class="items-table">
              <thead>
                <tr>
                  <th style="min-width:180px">Description</th>
                  <th style="width:78px">Unit</th>
                  <th style="width:70px">Qty</th>
                  <th style="width:110px">Unit Price (R)</th>
                  <th style="width:90px;text-align:right">Total</th>
                  <th style="width:32px"></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, idx) in qForm.items" :key="idx">
                  <td><input v-model="item.item_description" type="text" class="tbl-in" placeholder="e.g. Labour"></td>
                  <td><input v-model="item.unit" type="text" class="tbl-in" placeholder="hrs"></td>
                  <td><input v-model.number="item.quantity" type="number" class="tbl-in" min="0" step="0.01" @input="recalc"></td>
                  <td><input v-model.number="item.unit_price" type="number" class="tbl-in" min="0" step="0.01" @input="recalc"></td>
                  <td class="line-total">R {{ lineTotal(item) }}</td>
                  <td style="text-align:center">
                    <button type="button" class="del-btn" @click="removeItem(idx)" :disabled="qForm.items.length === 1">
                      <i class="bi bi-x-lg"></i>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <button type="button" class="add-item-btn" @click="addItem">
            <i class="bi bi-plus-lg"></i> Add Line Item
          </button>
        </div>

        <!-- Totals -->
        <div class="totals-card">
          <div class="t-row">
            <span class="lbl">Subtotal</span>
            <span class="val">R {{ fmt(totals.subtotal) }}</span>
          </div>
          <div class="t-row">
            <span class="lbl">VAT ({{ qForm.vat_rate }}%)</span>
            <span class="val">R {{ fmt(totals.vat_amount) }}</span>
          </div>
          <div class="t-divider"></div>
          <div class="t-row grand">
            <span>Total</span>
            <span class="grand-val">R {{ fmt(totals.total) }}</span>
          </div>
        </div>

        <!-- Notes -->
        <div class="section-head">Notes</div>
        <f7-list inset strong>
          <f7-list-input label="Notes (optional)" type="textarea" placeholder="Payment terms, special instructions…"
            :value="qForm.notes" @input="qForm.notes = $event.target.value" resizable>
            <template #media><i class="bi bi-sticky"></i></template>
          </f7-list-input>
        </f7-list>

        <!-- Images -->
        <div class="section-head">Images</div>
        <label class="upload-zone" @dragover.prevent @drop.prevent="onImgDrop">
          <input type="file" accept="image/*" multiple style="display:none" @change="onImgsSelected" />
          <i class="bi bi-cloud-arrow-up upload-zone-icon"></i>
          <div class="upload-zone-text">Tap to add photos</div>
          <div class="upload-zone-hint">JPEG, PNG, WEBP, HEIC</div>
        </label>
        <div v-if="uploadedImages.length" class="upload-preview-grid">
          <div v-for="(img, idx) in uploadedImages" :key="idx" class="upload-preview-item">
            <img :src="img.url" :alt="'Photo ' + (idx + 1)" />
            <span v-if="img.existing" class="upload-preview-saved">saved</span>
            <button type="button" class="upload-preview-remove" @click="removeUploadedImage(idx)">
              <i class="bi bi-x-lg"></i>
            </button>
          </div>
        </div>

        <!-- Actions -->
        <div class="form-actions">
          <!-- <button v-if="isEditing" class="btn-ghost" @click="cancelEdit" :disabled="quoteLoading">
            <i class="bi bi-x-lg"></i> Cancel
          </button> -->
          <button class="btn-primary" @click="isEditing ? submitEdit() : submitQuote()" :disabled="quoteLoading">
            <f7-preloader v-if="quoteLoading" :size="18" color="white"></f7-preloader>
            <i v-else :class="isEditing ? 'bi bi-floppy' : 'bi bi-send-fill'"></i>
            {{ quoteLoading ? (isEditing ? 'Saving…' : 'Submitting…') : (isEditing ? 'Save Changes' : 'Submit Quotation') }}
          </button>
        </div>

      </div>
    </template>

    <!-- ── DETAIL ────────────────────────────────────────────────────── -->
    <template v-if="view === 'detail'">

      <!-- Hero — visible immediately from list data -->
      <div :class="['detail-hero', 'dh-s-' + (selectedQuote.status || '')]">
        <div class="detail-hero-top">
          <div class="detail-hero-left">
            <div class="detail-quote-num">{{ selectedQuote.quote_number }}</div>
            <div class="detail-hero-customer">{{ selectedQuote.customer_name }}</div>
          </div>
          <div class="detail-hero-right">
            <span :class="['st-badge', 'st-' + selectedQuote.status]">
              {{ selectedQuote.status?.replace('_', ' ') }}
            </span>
            <span v-if="selectedQuote.type_name" class="detail-type-badge">
              <i class="bi bi-tag"></i> {{ selectedQuote.type_name }}
            </span>
          </div>
        </div>
        <div class="detail-big-total">R {{ fmt(selectedQuote.total) }}</div>
        <div class="detail-hero-contact">
          <span v-if="selectedQuote.customer_phone">
            <i class="bi bi-phone"></i> {{ selectedQuote.customer_phone }}
          </span>
          <span v-if="selectedQuote.customer_email">
            <i class="bi bi-envelope"></i> {{ selectedQuote.customer_email }}
          </span>
        </div>
      </div>

      <div v-if="detailLoading" class="list-spinner" style="padding-top:40px">
        <f7-preloader :size="32"></f7-preloader>
      </div>

      <div v-else-if="detailError" style="padding:16px">
        <div class="alert-err">
          <i class="bi bi-exclamation-circle-fill"></i>
          <span>{{ detailError }}</span>
        </div>
      </div>

      <div v-else class="detail-body">

        <!-- Date / meta grid -->
        <div class="detail-section-head">Details</div>
        <div class="detail-info-grid">
          <div class="detail-cell">
            <div class="detail-label">Quote Date</div>
            <div class="detail-value">{{ selectedQuote.quote_date }}</div>
          </div>
          <div class="detail-cell">
            <div class="detail-label">Valid Until</div>
            <div class="detail-value">{{ selectedQuote.valid_until || '—' }}</div>
          </div>
          <div v-if="selectedQuote.created_by" class="detail-cell">
            <div class="detail-label">Created By</div>
            <div class="detail-value">{{ selectedQuote.created_by }}</div>
          </div>
          <div v-if="selectedQuote.created_at" class="detail-cell">
            <div class="detail-label">Created At</div>
            <div class="detail-value">{{ selectedQuote.created_at?.slice(0,16) }}</div>
          </div>
          <div v-if="selectedQuote.description" class="detail-cell detail-cell-full">
            <div class="detail-label">Description</div>
            <div class="detail-value">{{ selectedQuote.description }}</div>
          </div>
        </div>

        <!-- Line items -->
        <div class="detail-section-head">
          Line Items
          <span class="detail-item-count">{{ selectedQuote.items?.length }}</span>
        </div>
        <div class="detail-items-wrap">
          <div v-for="(item, idx) in selectedQuote.items" :key="item.id" class="detail-item">
            <div class="detail-item-top">
              <span class="detail-item-num">{{ idx + 1 }}</span>
              <span class="detail-item-desc">{{ item.item_description }}</span>
              <span class="detail-item-total">R {{ fmt(item.line_total ?? item.quantity * item.unit_price) }}</span>
            </div>
            <div class="detail-item-sub">
              <span v-if="item.unit">{{ item.unit }}</span>
              <span>{{ item.quantity }} × R {{ fmt(item.unit_price) }}</span>
            </div>
          </div>
        </div>

        <!-- Financial summary -->
        <div class="detail-summary">
          <div class="detail-summary-row">
            <span>Subtotal</span>
            <span>R {{ fmt(selectedQuote.subtotal) }}</span>
          </div>
          <div class="detail-summary-row">
            <span>VAT ({{ selectedQuote.vat_rate }}%)</span>
            <span>R {{ fmt(selectedQuote.vat_amount) }}</span>
          </div>
          <div class="detail-summary-divider"></div>
          <div class="detail-summary-row detail-summary-total">
            <span>Total</span>
            <span>R {{ fmt(selectedQuote.total) }}</span>
          </div>
        </div>

        <!-- Images -->
        <template v-if="selectedQuote.images?.length">
          <div class="detail-section-head">
            Images
            <span class="detail-item-count">{{ selectedQuote.images.length }}</span>
          </div>
          <div class="detail-images-strip">
            <button
              v-for="(img, idx) in selectedQuote.images"
              :key="img.index"
              class="detail-img-thumb"
              @click="openPreview(idx)"
            >
              <img :src="img.url" :alt="'Image ' + img.index" loading="lazy" />
            </button>
          </div>
        </template>

        <!-- Notes -->
        <template v-if="selectedQuote.notes">
          <div class="detail-section-head">Notes</div>
          <div class="detail-notes">{{ selectedQuote.notes }}</div>
        </template>

        <!-- Meta footer -->
        <div v-if="selectedQuote.updated_at" class="detail-meta-footer">
          Last updated {{ selectedQuote.updated_at?.slice(0,16) }}
        </div>

        <!-- Actions -->
        <div class="detail-actions">
          <button class="btn-primary" @click="shareQuotation">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/>
              <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
            </svg>
            Print / Share
          </button>
          <div v-if="shareCopied" class="share-copied">
            <i class="bi bi-check2-circle"></i> Copied to clipboard
          </div>
        </div>

      </div>
    </template>

    <!-- ── SUCCESS ────────────────────────────────────────────────────── -->
    <template v-if="view === 'success'">
      <div class="success-wrap">

        <div class="success-icon"><i class="bi bi-check-circle-fill"></i></div>
        <h2 class="success-title">Quotation Created!</h2>
        <p class="success-sub">Your quotation has been saved successfully.</p>
        <div class="quote-pill">{{ createdQuote.quote_number }}</div>

        <div class="success-card">
          <div class="sc-row">
            <span class="sc-lbl">Customer</span>
            <strong>{{ createdQuote.customer_name }}</strong>
          </div>
          <div class="sc-row">
            <span class="sc-lbl">Quote Date</span>
            <span>{{ createdQuote.quote_date }}</span>
          </div>
          <div class="sc-row">
            <span class="sc-lbl">Items</span>
            <span>{{ createdQuote.items_count }}</span>
          </div>
          <div class="sc-row">
            <span class="sc-lbl">Subtotal</span>
            <span>R {{ fmt(createdQuote.subtotal) }}</span>
          </div>
          <div class="sc-row">
            <span class="sc-lbl">VAT ({{ createdQuote.vat_rate }}%)</span>
            <span>R {{ fmt(createdQuote.vat_amount) }}</span>
          </div>
          <div class="sc-divider"></div>
          <div class="sc-row sc-total">
            <strong>Total</strong>
            <strong class="sc-total-val">R {{ fmt(createdQuote.total) }}</strong>
          </div>
        </div>

        <div style="display:flex;gap:12px;flex-wrap:wrap;justify-content:center">
          <button class="btn-ghost" @click="goToList">
            <i class="bi bi-files"></i> View All
          </button>
          <button class="btn-primary" @click="goToCreate">
            <i class="bi bi-plus-lg"></i> New Quote
          </button>
        </div>

      </div>
    </template>

  <!-- ── IMAGE LIGHTBOX ──────────────────────────────────────────────── -->
  <div v-if="previewOpen" class="lightbox" @click.self="closePreview">
    <button class="lb-close" @click="closePreview">
      <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
      </svg>
    </button>

    <button v-if="previewIndex > 0" class="lb-nav lb-prev" @click="previewIndex--">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="15 18 9 12 15 6"/>
      </svg>
    </button>

    <img class="lb-img" :src="selectedQuote.images[previewIndex]?.url" :key="previewIndex" />

    <button v-if="previewIndex < selectedQuote.images.length - 1" class="lb-nav lb-next" @click="previewIndex++">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="9 18 15 12 9 6"/>
      </svg>
    </button>

    <div class="lb-counter">{{ previewIndex + 1 }} / {{ selectedQuote.images.length }}</div>
    <button class="lb-close-btn" @click="closePreview">
      <i class="bi bi-x-lg"></i> Close
    </button>
  </div>

  </f7-page>
</template>

<script>
import { ref, reactive, computed, onMounted, watch } from 'vue';

const API_BASE = 'http://mul-admin.com/api';

export default {
  setup() {

    const f7params = {
      name: 'Demo QT',
      theme: 'md',
      darkMode: true,
      colors: { primary: '#e94560' },
    };

    // ── State ─────────────────────────────────────────────────────────────
    const view         = ref('login');
    const user         = ref({});
    const showPw       = ref(false);
    const menuOpen     = ref(false);
    const loginLoading = ref(false);
    const loginError   = ref('');
    const quoteLoading = ref(false);
    const quoteError   = ref('');
    const createdQuote = ref({});

    // ── Users state (admin) ───────────────────────────────────────────────
    const users             = ref([]);
    const usersLoading      = ref(false);
    const usersError        = ref('');
    const userSearch        = ref('');
    const selectedUser      = ref(null);
    const groups            = ref([]);
    const groupsLoading     = ref(false);
    const showUserPw        = ref(false);
    const userCreateLoading = ref(false);
    const userCreateError   = ref('');
    const userForm          = reactive({ username: '', email: '', password: '', group_id: '', is_active: true });

    // ── Password change state ─────────────────────────────────────────────
    const passwordUser  = ref(null);
    const pwReturnView  = ref('users');
    const pwForm        = reactive({ password: '', confirm: '' });
    const showNewPw     = ref(false);
    const showConfirmPw = ref(false);
    const pwLoading     = ref(false);
    const pwError       = ref('');
    const pwSuccess     = ref(false);

    const filteredUsers = computed(() => {
      const q = userSearch.value.trim().toLowerCase();
      if (!q) return users.value;
      return users.value.filter(u =>
        u.username?.toLowerCase().includes(q) ||
        u.email?.toLowerCase().includes(q) ||
        u.group_name?.toLowerCase().includes(q)
      );
    });

    const isAdmin  = computed(() => user.value?.group_name?.toLowerCase() === 'admin');
    const homeView = computed(() => isAdmin.value ? 'users' : 'list');

    // ── List state ────────────────────────────────────────────────────────
    const quotations        = ref([]);
    const quotationsLoading = ref(false);
    const quotationsError   = ref('');
    const listFilter        = ref('all');
    const searchQuery       = ref('');
    const dateFrom          = ref('');
    const dateTo            = ref('');

    const statusFilters = [
      { value: 'all',         label: 'All' },
      { value: 'draft',       label: 'Draft' },
      { value: 'sent',        label: 'Sent' },
      { value: 'accepted',    label: 'Accepted' },
      { value: 'in_progress', label: 'In Progress' },
      { value: 'completed',   label: 'Completed' },
      { value: 'invoiced',    label: 'Invoiced' },
      { value: 'rejected',    label: 'Rejected' },
      { value: 'cancelled',   label: 'Cancelled' },
    ];

    const hasActiveFilters = computed(() =>
      !!searchQuery.value || !!dateFrom.value || !!dateTo.value
    );

    const filteredQuotations = computed(() => {
      let list = listFilter.value === 'all'
        ? quotations.value
        : quotations.value.filter(q => q.status === listFilter.value);

      const q = searchQuery.value.trim().toLowerCase();
      if (q) {
        list = list.filter(x =>
          x.quote_number?.toLowerCase().includes(q) ||
          x.customer_name?.toLowerCase().includes(q) ||
          x.customer_email?.toLowerCase().includes(q) ||
          x.customer_phone?.toLowerCase().includes(q)
        );
      }

      if (dateFrom.value) list = list.filter(x => x.quote_date >= dateFrom.value);
      if (dateTo.value)   list = list.filter(x => x.quote_date <= dateTo.value);

      return list;
    });

    const callPhone = (phone) => {
      const url = 'tel:' + phone;
      if (window.cordova?.InAppBrowser) {
        window.cordova.InAppBrowser.open(url, '_system');
      } else {
        window.location.href = url;
      }
    };

    const clearFilters = () => {
      searchQuery.value = '';
      dateFrom.value    = '';
      dateTo.value      = '';
    };

    // ── Detail state ──────────────────────────────────────────────────────
    const selectedQuote = ref({});
    const detailLoading = ref(false);
    const detailError   = ref('');
    const shareCopied   = ref(false);

    // ── Image preview ─────────────────────────────────────────────────────
    const previewOpen  = ref(false);
    const previewIndex = ref(0);

    // ── Image upload (create / edit form) ────────────────────────────────
    // Each entry: { file, url, existing, index }
    // existing=true  → already on server (index = slot 1-4, file = null)
    // existing=false → local file pending upload
    const uploadedImages    = ref([]);
    const removedImageSlots = ref([]);

    const onImgsSelected = (e) => {
      Array.from(e.target.files || []).forEach(file => {
        uploadedImages.value.push({ file, url: URL.createObjectURL(file), existing: false });
      });
      e.target.value = '';
    };

    const onImgDrop = (e) => {
      Array.from(e.dataTransfer.files || [])
        .filter(f => f.type.startsWith('image/'))
        .forEach(file => uploadedImages.value.push({ file, url: URL.createObjectURL(file), existing: false }));
    };

    const removeUploadedImage = (idx) => {
      const img = uploadedImages.value[idx];
      if (img.existing) {
        removedImageSlots.value.push(img.index);
      } else {
        URL.revokeObjectURL(img.url);
      }
      uploadedImages.value.splice(idx, 1);
    };

    const apiUpload = async (path, formData) => {
      const res = await fetch(API_BASE + path, {
        method: 'POST',
        headers: { Authorization: 'Bearer ' + (localStorage.getItem('qt_token') || '') },
        body: formData,
      });
      const body = await res.json().catch(() => ({}));
      if (!res.ok) throw { response: { status: res.status, data: body } };
      return body;
    };

    const openPreview = (idx) => {
      previewIndex.value = idx;
      previewOpen.value  = true;
    };
    const closePreview = () => { previewOpen.value = false; };

    const loginForm = reactive({ login: '', password: '' });

    const blankItem = () => ({ item_description: '', unit: '', quantity: 1, unit_price: 0 });
    const today     = () => new Date().toISOString().slice(0, 10);

    const qForm = reactive({
      customer_name:  '',
      customer_phone: '',
      customer_email: '',
      description:    '',
      quote_date:     today(),
      valid_until:    '',
      vat_rate:       15,
      notes:          '',
      items:          [blankItem()],
    });

    // ── Computed totals ───────────────────────────────────────────────────
    const totals = computed(() => {
      const subtotal   = qForm.items.reduce((s, i) => s + lineRaw(i), 0);
      const vat_amount = Math.round(subtotal * qForm.vat_rate / 100 * 100) / 100;
      return {
        subtotal:   Math.round(subtotal   * 100) / 100,
        vat_amount: vat_amount,
        total:      Math.round((subtotal + vat_amount) * 100) / 100,
      };
    });

    // ── Helpers ───────────────────────────────────────────────────────────
    const lineRaw   = i => Math.round(Math.max(0, i.quantity) * Math.max(0, i.unit_price) * 100) / 100;
    const lineTotal = i => fmt(lineRaw(i));
    const fmt       = v => Number(v || 0).toLocaleString('en-ZA', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const recalc    = () => {};

    const apiFetch = async (path, options = {}) => {
      const res = await fetch(API_BASE + path, {
        ...options,
        headers: {
          'Content-Type': 'application/json',
          Authorization: 'Bearer ' + (localStorage.getItem('qt_token') || ''),
          ...(options.headers || {}),
        },
      });
      const body = await res.json().catch(() => ({}));
      if (!res.ok) throw { response: { status: res.status, data: body } };
      return body;
    };

    // ── Users functions (admin) ───────────────────────────────────────────
    const fetchUsers = async () => {
      usersLoading.value = true;
      usersError.value   = '';
      try {
        const data = await apiFetch('/users');
        users.value = data.data ?? data;
      } catch (err) {
        if (err.response?.status === 401) { logout(); return; }
        usersError.value = err.response?.data?.error || 'Failed to load users.';
      } finally {
        usersLoading.value = false;
      }
    };

    const openUser = (u) => {
      selectedUser.value = u;
      view.value = 'list';
      fetchQuotations(u.id);
    };

    const goToUsers = () => {
      selectedUser.value = null;
      view.value = 'users';
      fetchUsers();
    };

    const fetchGroups = async () => {
      groupsLoading.value = true;
      try {
        const data = await apiFetch('/groups');
        groups.value = data.data ?? data;
      } catch (_) {}
      finally { groupsLoading.value = false; }
    };

    const goToUserCreate = () => {
      userCreateError.value = '';
      showUserPw.value = false;
      Object.assign(userForm, { username: '', email: '', password: '', group_id: '', is_active: true });
      if (!groups.value.length) fetchGroups();
      view.value = 'user-create';
    };

    const submitUser = async () => {
      userCreateError.value   = '';
      userCreateLoading.value = true;
      try {
        await apiFetch('/users', {
          method: 'POST',
          body: JSON.stringify({
            username:  userForm.username.trim(),
            email:     userForm.email.trim(),
            password:  userForm.password,
            group_id:  userForm.group_id,
            is_active: userForm.is_active ? 1 : 0,
          }),
        });
        goToUsers();
      } catch (err) {
        const d = err.response?.data;
        userCreateError.value = d?.details || d?.error || 'Failed to create user. Please try again.';
        if (err.response?.status === 401) { setTimeout(logout, 2000); }
      } finally {
        userCreateLoading.value = false;
      }
    };

    const _openPasswordView = (u, returnView) => {
      passwordUser.value  = u;
      pwReturnView.value  = returnView;
      pwForm.password     = '';
      pwForm.confirm      = '';
      pwError.value       = '';
      pwSuccess.value     = false;
      showNewPw.value     = false;
      showConfirmPw.value = false;
      view.value = 'user-password';
    };

    const goToChangePassword = (u) => _openPasswordView(u, 'users');
    const goToMyPassword     = ()  => _openPasswordView(user.value, homeView.value);

    const submitPassword = async () => {
      pwError.value   = '';
      pwSuccess.value = false;

      if (pwForm.password.length < 6) {
        pwError.value = 'Password must be at least 6 characters.';
        return;
      }
      if (pwForm.password !== pwForm.confirm) {
        pwError.value = 'Passwords do not match.';
        return;
      }

      pwLoading.value = true;
      try {
        await apiFetch(`/users/${passwordUser.value.id}/password`, {
          method: 'PUT',
          body: JSON.stringify({ password: pwForm.password }),
        });
        pwSuccess.value = true;
        pwForm.password = '';
        pwForm.confirm  = '';
      } catch (err) {
        const d = err.response?.data;
        pwError.value = d?.details || d?.error || 'Failed to update password. Please try again.';
        if (err.response?.status === 401) { setTimeout(logout, 2000); }
      } finally {
        pwLoading.value = false;
      }
    };

    // ── List functions ────────────────────────────────────────────────────
    const fetchQuotations = async (userId = null) => {
      quotationsLoading.value = true;
      quotationsError.value   = '';
      try {
        const path = userId ? `/quotations?user_id=${userId}` : '/quotations';
        const data = await apiFetch(path);
        quotations.value = data.data ?? data;
      } catch (err) {
        if (err.response?.status === 401) { logout(); return; }
        quotationsError.value = err.response?.data?.error || 'Failed to load quotations.';
      } finally {
        quotationsLoading.value = false;
      }
    };

    const setListFilter = status => { listFilter.value = status; };

    const openQuotation = async (q) => {
      selectedQuote.value = { ...q };
      view.value          = 'detail';
      detailLoading.value = true;
      detailError.value   = '';
      try {
        const data = await apiFetch(`/quotation/${q.id}`);
        selectedQuote.value = data.data ?? data.quote ?? data;
      } catch (err) {
        if (err.response?.status === 401) { logout(); return; }
        detailError.value = err.response?.data?.error || 'Failed to load quotation details.';
      } finally {
        detailLoading.value = false;
      }
    };

    const buildQuotationHTML = (q) => {
      const rows = (q.items || []).map((i, n) => `
        <tr>
          <td>${n + 1}</td>
          <td>${i.item_description}</td>
          <td>${i.unit || ''}</td>
          <td class="r">${i.quantity}</td>
          <td class="r">R&nbsp;${fmt(i.unit_price)}</td>
          <td class="r"><strong>R&nbsp;${fmt(i.line_total ?? i.quantity * i.unit_price)}</strong></td>
        </tr>`).join('');

      const images = (q.images || []).map(img =>
        `<img src="${img.url}" alt="Image ${img.index}" style="max-width:180px;max-height:180px;object-fit:cover;border-radius:8px;margin:4px">`
      ).join('');

      return `<!DOCTYPE html><html lang="en"><head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Quotation ${q.quote_number}</title>
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#1a1a1a;padding:28px;max-width:720px;margin:0 auto}
  .hdr{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:28px;padding-bottom:20px;border-bottom:2px solid #e94560}
  .co-name{font-size:22px;font-weight:800;letter-spacing:-.02em}
  .co-sub{font-size:11px;opacity:.45;margin-top:3px}
  .q-num{font-size:20px;font-weight:800;color:#e94560;text-align:right}
  .q-date{font-size:12px;opacity:.5;margin-top:3px;text-align:right}
  .badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;background:#f0f0f0;margin-top:6px}
  .info{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px}
  .lbl{font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;opacity:.35;margin-bottom:5px}
  .val{font-size:15px;font-weight:700}
  .sub{font-size:12px;opacity:.55;margin-top:2px}
  table{width:100%;border-collapse:collapse;margin-bottom:16px}
  th{background:#f5f5f5;padding:8px 10px;text-align:left;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;opacity:.6}
  td{padding:9px 10px;border-bottom:1px solid #f0f0f0;vertical-align:top}
  .r{text-align:right}
  .totals{width:260px;margin-left:auto;border-top:1px solid #eee;padding-top:10px}
  .tr{display:flex;justify-content:space-between;padding:4px 0;font-size:13px}
  .tdiv{border-top:2px solid #1a1a1a;margin:8px 0}
  .tgrand{font-size:17px;font-weight:800}
  .notes{background:#f9f9f9;border-radius:8px;padding:14px;margin-top:20px;font-size:12px;line-height:1.5}
  .imgs{margin-top:20px;display:flex;flex-wrap:wrap;gap:6px}
</style>
</head><body>
<div class="hdr">
  <div>
    <div class="co-name">Bulise</div>
    <div class="co-sub">Quotation Management</div>
  </div>
  <div>
    <div class="q-num">${q.quote_number}</div>
    <div class="q-date">${q.quote_date}</div>
    <span class="badge">${(q.status || '').replace('_', ' ')}</span>
  </div>
</div>
<div class="info">
  <div>
    <div class="lbl">Customer</div>
    <div class="val">${q.customer_name}</div>
    ${q.customer_phone ? `<div class="sub">${q.customer_phone}</div>` : ''}
    ${q.customer_email ? `<div class="sub">${q.customer_email}</div>` : ''}
    ${q.description    ? `<div class="sub" style="margin-top:8px">${q.description}</div>` : ''}
  </div>
  <div>
    <div class="lbl">Quote Details</div>
    <div class="sub">Date: ${q.quote_date}</div>
    ${q.valid_until ? `<div class="sub">Valid until: ${q.valid_until}</div>` : ''}
    ${q.created_by  ? `<div class="sub">Prepared by: ${q.created_by}</div>` : ''}
  </div>
</div>
<div class="lbl" style="margin-bottom:8px">Line Items</div>
<table>
  <thead><tr>
    <th>#</th><th>Description</th><th>Unit</th>
    <th class="r">Qty</th><th class="r">Unit Price</th><th class="r">Amount</th>
  </tr></thead>
  <tbody>${rows}</tbody>
</table>
<div class="totals">
  <div class="tr"><span style="opacity:.5">Subtotal</span><span>R&nbsp;${fmt(q.subtotal)}</span></div>
  <div class="tr"><span style="opacity:.5">VAT (${q.vat_rate}%)</span><span>R&nbsp;${fmt(q.vat_amount)}</span></div>
  <div class="tdiv"></div>
  <div class="tr tgrand"><span>Total</span><span>R&nbsp;${fmt(q.total)}</span></div>
</div>
${q.notes  ? `<div class="notes"><div class="lbl" style="margin-bottom:6px">Notes</div>${q.notes}</div>` : ''}
${images   ? `<div class="imgs">${images}</div>` : ''}
</body></html>`;
    };

    const markAsSent = async () => {
      const q = selectedQuote.value;
      if (q.status !== 'draft') return;
      const payload = {
        customer_name:  q.customer_name  || '',
        customer_phone: q.customer_phone || '',
        customer_email: q.customer_email || '',
        description:    q.description    || '',
        quote_date:     q.quote_date,
        valid_until:    q.valid_until    || undefined,
        vat_rate:       q.vat_rate       ?? 15,
        notes:          q.notes          || '',
        status:         'sent',
        items: (q.items || []).map(i => ({
          item_description: i.item_description,
          unit:             i.unit || '',
          quantity:         i.quantity,
          unit_price:       i.unit_price,
        })),
      };
      try {
        await apiFetch(`/quotation/${q.id}`, { method: 'PUT', body: JSON.stringify(payload) });
        selectedQuote.value = { ...q, status: 'sent' };
      } catch (_) {}
    };

    const shareQuotation = () => {
      const q     = selectedQuote.value;
      const phone = (q.customer_phone || '').replace(/[\s\-().]/g, '');

      const html = buildQuotationHTML({ ...q, images: [] });

      const savePath = cordova.platformId === 'ios' ? '~/Documents/output.pdf' : 'output.pdf';

      cordova.plugins.html2pdf.create(
        html,
        savePath,
        async (pdfPath) => {
          const message  = `Hi ${q.customer_name}, please find your quotation ${q.quote_number} attached.`;
          const filePath = 'file://' + pdfPath;

          if (window.plugins?.socialsharing) {
            if (phone) {
              window.plugins.socialsharing.shareViaWhatsAppToPhone(
                phone, message, null, filePath,
                () => {},
                () => {
                  // phone-targeted share failed — fall back to WhatsApp picker
                  window.plugins.socialsharing.shareVia(
                    'whatsapp', message, null, filePath,
                    () => {}, (err) => console.error('WhatsApp share error:', err)
                  );
                }
              );
            } else {
              window.plugins.socialsharing.shareVia(
                'whatsapp', message, null, filePath,
                () => {}, (err) => console.error('WhatsApp share error:', err)
              );
            }
          }

          await markAsSent();
        },
        (error) => console.error('Error creating PDF:', error)
      );
    };

    // ── Edit state ────────────────────────────────────────────────────────
    const isEditing  = ref(false);
    const editingId  = ref(null);
    const editStatus = ref('');

    const goToEdit = async (listQuote = null) => {
      let q = listQuote || selectedQuote.value;
      // List cards don't carry line items — fetch full data first
      if (!q.items) {
        try {
          const data = await apiFetch(`/quotation/${q.id}`);
          q = data.data ?? data.quote ?? data;
        } catch (err) {
          if (err.response?.status === 401) logout();
          return;
        }
      }
      selectedQuote.value = q;
      quoteError.value = '';
      uploadedImages.value.forEach(img => { if (!img.existing) URL.revokeObjectURL(img.url); });
      uploadedImages.value    = (q.images || []).map(img => ({ file: null, url: img.url, existing: true, index: img.index }));
      removedImageSlots.value = [];
      editingId.value  = q.id;
      editStatus.value = q.status || 'draft';
      Object.assign(qForm, {
        customer_name:  q.customer_name  || '',
        customer_phone: q.customer_phone || '',
        customer_email: q.customer_email || '',
        description:    q.description    || '',
        quote_date:     q.quote_date     || today(),
        valid_until:    q.valid_until    || '',
        vat_rate:       q.vat_rate       ?? 15,
        notes:          q.notes          || '',
        items: (q.items || []).length
          ? q.items.map(i => ({
              item_description: i.item_description || '',
              unit:             i.unit             || '',
              quantity:         Number(i.quantity) || 1,
              unit_price:       Number(i.unit_price) || 0,
            }))
          : [blankItem()],
      });
      isEditing.value = true;
      view.value = 'create';
    };

    const cancelEdit = () => {
      isEditing.value  = false;
      editingId.value  = null;
      editStatus.value = '';
      quoteError.value = '';
      uploadedImages.value.forEach(img => { if (!img.existing) URL.revokeObjectURL(img.url); });
      uploadedImages.value    = [];
      removedImageSlots.value = [];
      view.value = 'detail';
    };

    const submitEdit = async () => {
      quoteError.value   = '';
      quoteLoading.value = true;

      if (!qForm.customer_name.trim()) { quoteError.value = 'Customer name is required.'; quoteLoading.value = false; return; }
      if (!qForm.quote_date)           { quoteError.value = 'Quote date is required.';    quoteLoading.value = false; return; }
      const validItems = qForm.items.filter(i => i.item_description.trim());
      if (!validItems.length)          { quoteError.value = 'Add at least one line item.'; quoteLoading.value = false; return; }

      try {
        const payload = {
          customer_name:  qForm.customer_name.trim(),
          customer_phone: qForm.customer_phone.trim(),
          customer_email: qForm.customer_email.trim(),
          description:    qForm.description.trim(),
          quote_date:     qForm.quote_date,
          valid_until:    qForm.valid_until || undefined,
          vat_rate:       qForm.vat_rate,
          notes:          qForm.notes.trim(),
          status:         editStatus.value,
          items:          validItems,
          ...(removedImageSlots.value.length && { remove_image_slots: removedImageSlots.value }),
        };

        await apiFetch(`/quotation/${editingId.value}`, {
          method: 'PUT',
          body: JSON.stringify(payload),
        });

        const newImages = uploadedImages.value.filter(img => !img.existing);
        if (newImages.length) {
          const fd = new FormData();
          newImages.forEach(img => fd.append('images[]', img.file));
          await apiUpload(`/quotation/${editingId.value}/images`, fd).catch(() => {});
        }

        const id = editingId.value;
        isEditing.value  = false;
        editingId.value  = null;
        editStatus.value = '';
        uploadedImages.value.forEach(img => { if (!img.existing) URL.revokeObjectURL(img.url); });
        uploadedImages.value    = [];
        removedImageSlots.value = [];
        await openQuotation({ id });
      } catch (err) {
        const d = err.response?.data;
        if (d?.details)    quoteError.value = d.details;
        else if (d?.error) quoteError.value = d.error;
        else               quoteError.value = 'Update failed. Please try again.';

        if (err.response?.status === 401) {
          quoteError.value = 'Session expired. Please sign in again.';
          setTimeout(logout, 2000);
        }
      } finally {
        quoteLoading.value = false;
      }
    };

    const goToList = () => {
      selectedUser.value = null;
      view.value = 'list';
      fetchQuotations();
    };

    const goBack = () => {
      if (view.value === 'user-password') {
        if (pwReturnView.value === 'users') goToUsers();
        else goToList();
      } else if (view.value === 'user-create') {
        goToUsers();
      } else if (view.value === 'create' && isEditing.value) {
        cancelEdit();
      } else if (view.value === 'create') {
        if (selectedUser.value) {
          view.value = 'list';
          fetchQuotations(selectedUser.value.id);
        } else {
          goToList();
        }
      } else if (view.value === 'detail') {
        view.value = 'list';
        fetchQuotations(selectedUser.value?.id || null);
      } else if (view.value === 'list' && selectedUser.value) {
        goToUsers();
      } else {
        goToList();
      }
    };

    const goToCreate = () => {
      resetForm();
      view.value = 'create';
    };

    // ── Auth ──────────────────────────────────────────────────────────────
    const doLogin = async () => {
      loginError.value   = '';
      loginLoading.value = true;
      try {
        const data = await apiFetch('/login', {
          method: 'POST',
          body: JSON.stringify({ login: loginForm.login, password: loginForm.password }),
        });
        localStorage.setItem('qt_token', data.token);
        localStorage.setItem('qt_user',  JSON.stringify(data.user));
        user.value = data.user;
        loginForm.password = '';
        if (data.user.group_name?.toLowerCase() === 'admin') {
          goToUsers();
        } else {
          goToList();
        }
      } catch (err) {
        loginError.value = err.response?.data?.error || 'Login failed. Check your credentials.';
      } finally {
        loginLoading.value = false;
      }
    };

    const logout = () => {
      localStorage.removeItem('qt_token');
      localStorage.removeItem('qt_user');
      user.value           = {};
      view.value           = 'login';
      loginForm.login      = '';
      loginForm.password   = '';
      loginError.value     = '';
      quotations.value     = [];
      listFilter.value     = 'all';
      searchQuery.value    = '';
      dateFrom.value       = '';
      dateTo.value         = '';
      users.value          = [];
      selectedUser.value   = null;
      userSearch.value     = '';
    };

    // ── Quotation ─────────────────────────────────────────────────────────
    const submitQuote = async () => {
      quoteError.value   = '';
      quoteLoading.value = true;

      if (!qForm.customer_name.trim()) { quoteError.value = 'Customer name is required.'; quoteLoading.value = false; return; }
      if (!qForm.quote_date)           { quoteError.value = 'Quote date is required.';    quoteLoading.value = false; return; }
      const validItems = qForm.items.filter(i => i.item_description.trim());
      if (!validItems.length)          { quoteError.value = 'Add at least one line item.'; quoteLoading.value = false; return; }

      try {
        const payload = {
          customer_name:  qForm.customer_name.trim(),
          customer_phone: qForm.customer_phone.trim(),
          customer_email: qForm.customer_email.trim(),
          description:    qForm.description.trim(),
          quote_date:     qForm.quote_date,
          valid_until:    qForm.valid_until || undefined,
          vat_rate:       qForm.vat_rate,
          notes:          qForm.notes.trim(),
          items:          validItems,
        };

        const data = await apiFetch('/quotation', { method: 'POST', body: JSON.stringify(payload) });

        const quoteId = data.quote?.id ?? data.id;
        if (uploadedImages.value.length && quoteId) {
          const fd = new FormData();
          uploadedImages.value.forEach(img => fd.append('images[]', img.file));
          await apiUpload(`/quotation/${quoteId}/images`, fd).catch(() => {});
        }

        createdQuote.value = {
          quote_number:  data.quote_number,
          customer_name: payload.customer_name,
          quote_date:    payload.quote_date,
          items_count:   data.items.length,
          subtotal:      data.quote.subtotal,
          vat_rate:      data.quote.vat_rate,
          vat_amount:    data.quote.vat_amount,
          total:         data.quote.total,
        };
        view.value = 'success';
      } catch (err) {
        const d = err.response?.data;
        if (d?.details)    quoteError.value = d.details;
        else if (d?.error) quoteError.value = d.error;
        else               quoteError.value = 'Submission failed. Please try again.';

        if (err.response?.status === 401) {
          quoteError.value = 'Session expired. Please sign in again.';
          setTimeout(logout, 2000);
        }
      } finally {
        quoteLoading.value = false;
      }
    };

    const addItem    = () => qForm.items.push(blankItem());
    const removeItem = idx => qForm.items.splice(idx, 1);

    const resetForm = () => {
      quoteError.value = '';
      isEditing.value  = false;
      editingId.value  = null;
      editStatus.value = '';
      uploadedImages.value.forEach(img => { if (!img.existing) URL.revokeObjectURL(img.url); });
      uploadedImages.value    = [];
      removedImageSlots.value = [];
      Object.assign(qForm, {
        customer_name: '', customer_phone: '', customer_email: '',
        description: '', quote_date: today(), valid_until: '',
        vat_rate: 15, notes: '', items: [blankItem()],
      });
    };

    const newQuote = () => {
      resetForm();
      view.value = 'create';
    };

    watch(view, () => { menuOpen.value = false; });

    // ── Restore session on mount ──────────────────────────────────────────
    onMounted(() => {
      const token  = localStorage.getItem('qt_token');
      const stored = localStorage.getItem('qt_user');
      if (token && stored) {
        user.value = JSON.parse(stored);
        if (user.value.group_name?.toLowerCase() === 'admin') {
          goToUsers();
        } else {
          goToList();
        }
      }
    });

    return {
      f7params,
      view, user, showPw, menuOpen,
      loginLoading, loginError, loginForm, doLogin, logout,
      quoteLoading, quoteError, qForm, totals, createdQuote,
      submitQuote, addItem, removeItem, resetForm,
      quotations, quotationsLoading, quotationsError, listFilter, statusFilters,
      searchQuery, dateFrom, dateTo, hasActiveFilters, clearFilters, callPhone,
      filteredQuotations, fetchQuotations, setListFilter, goToList, goToCreate,
      selectedQuote, detailLoading, detailError, shareCopied, openQuotation, shareQuotation,
      previewOpen, previewIndex, openPreview, closePreview,
      uploadedImages, removedImageSlots, onImgsSelected, onImgDrop, removeUploadedImage,
      isEditing, editStatus, goToEdit, cancelEdit, submitEdit, goBack,
      lineTotal, recalc, fmt,
      users, usersLoading, usersError, userSearch, filteredUsers,
      selectedUser, isAdmin, homeView, fetchUsers, openUser, goToUsers,
      groups, groupsLoading, showUserPw, userCreateLoading, userCreateError, userForm,
      goToUserCreate, submitUser,
      passwordUser, pwForm, showNewPw, showConfirmPw, pwLoading, pwError, pwSuccess,
      goToChangePassword, goToMyPassword, submitPassword,
    };
  }
};
</script>
