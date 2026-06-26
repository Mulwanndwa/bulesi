<template>
  <f7-page name="home" :class="{ 'is-chat': view === 'chat' }">

    <!-- ── NAVBAR ────────────────────────────────────────────────────── -->
    <f7-navbar v-if="view !== 'login' && view !== 'register'">
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
          {{ user.username }}
        </div>
      </f7-nav-left>
      <f7-nav-title v-if="view === 'conversations'">Messages</f7-nav-title>
      <f7-nav-title v-else-if="view === 'chat' && selectedConv">
        {{ selectedConv.participants.map(p => (p.first_name && p.last_name) ? p.first_name + ' ' + p.last_name : p.username).join(', ') }}
      </f7-nav-title>

      <f7-nav-right>
        <button v-if="view !== 'login' && view !== 'register'" class="nav-chat-btn" @click="goToConversations">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
          </svg>
          <span v-if="totalUnread > 0" class="nav-chat-badge">{{ totalUnread > 9 ? '9+' : totalUnread }}</span>
        </button>
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
            <button v-if="view === 'companies'" class="nav-drop-item" @click="fetchCompanies(); menuOpen = false">
              <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/>
                <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>
              </svg>
              Refresh
            </button>
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

            <!-- View public page -->
            <button v-if="view === 'detail' && selectedQuote.public_token" class="nav-drop-item" @click="openPublicView(); menuOpen = false">
              <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                <polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/>
              </svg>
              Customer View
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
    <!-- <f7-toolbar v-if="view !== 'login' && view !== 'register'" tabbar bottom icons>
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
            <div class="login-logo"><img src="../img/logo.png" alt="Bulise" style="width:150px;height:auto;object-fit:contain;" /></div>
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
                <svg v-if="!showPw" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
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

          <div class="login-footer">
            Don't have an account?
            <button class="login-link" @click="goToRegister">Create account</button>
          </div>

        </div>
      </div>
    </template>

    <!-- ── REGISTER ───────────────────────────────────────────────────── -->
    <template v-if="view === 'register'">
      <div class="login-wrap">
        <div class="login-card">

          <div class="login-brand">
            <div class="login-logo"><img src="../img/logo.png" alt="Bulise" style="width:150px;height:auto;object-fit:contain;" /></div>
            <div class="login-tagline">Create your account</div>
          </div>

          <div v-if="regError" class="alert-err">
            <i class="bi bi-exclamation-circle-fill"></i>
            <span>{{ regError }}</span>
          </div>

          <div class="lf-group">
            <label class="lf-label">First Name</label>
            <div class="lf-input-wrap">
              <input class="lf-input" type="text" placeholder="First"
                :value="regForm.first_name" @input="regForm.first_name = $event.target.value"
                autocomplete="given-name" style="padding-left:12px" />
            </div>
          </div>
          <div class="lf-group">
            <label class="lf-label">Last Name</label>
            <div class="lf-input-wrap">
              <input class="lf-input" type="text" placeholder="Last"
                :value="regForm.last_name" @input="regForm.last_name = $event.target.value"
                autocomplete="family-name" style="padding-left:12px" />
            </div>
          </div>

          <div class="lf-group">
            <label class="lf-label">Username</label>
            <div class="lf-input-wrap">
              <!-- <i class="bi bi-person lf-icon"></i> -->
              <input class="lf-input" type="text" placeholder="your username"
                :value="regForm.username" @input="regForm.username = $event.target.value"
                autocomplete="username" />
            </div>
          </div>

          <div class="lf-group">
            <label class="lf-label">Email</label>
            <div class="lf-input-wrap">
              <!-- <i class="bi bi-envelope lf-icon"></i> -->
              <input class="lf-input" type="email" placeholder="you@example.com"
                :value="regForm.email" @input="regForm.email = $event.target.value"
                autocomplete="email" />
            </div>
          </div>

          <div class="lf-group">
            <label class="lf-label">Password</label>
            <div class="lf-input-wrap">
              <!-- <i class="bi bi-lock lf-icon"></i> -->
              <input class="lf-input" :type="showRegPw ? 'text' : 'password'" placeholder="min. 6 characters"
                :value="regForm.password" @input="regForm.password = $event.target.value"
                autocomplete="new-password" />
              <button type="button" class="lf-pw-btn" @click="showRegPw = !showRegPw">
                <i :class="showRegPw ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
              </button>
            </div>
          </div>

          <div class="lf-group">
            <label class="lf-label">Company</label>
            <div class="lf-input-wrap" style="padding-right:12px">
              <!-- <i class="bi bi-building lf-icon"></i> -->
              <select class="lf-input" style="cursor:pointer"
                :value="regForm.company_id" @change="regForm.company_id = $event.target.value">
                <option value="" disabled>Select your company…</option>
                <option v-if="regCompaniesLoading" disabled>Loading…</option>
                <option v-for="c in regCompanies" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
            </div>
          </div>

          <button class="login-btn" @click="doRegister"
            :disabled="regLoading || !regForm.first_name || !regForm.last_name || !regForm.username || !regForm.email || !regForm.password || !regForm.company_id">
            <f7-preloader v-if="regLoading" :size="18" color="white"></f7-preloader>
            <i v-else class="bi bi-person-plus"></i>
            {{ regLoading ? 'Creating account…' : 'Create Account' }}
          </button>

          <div class="login-footer">
            Already have an account?
            <button class="login-link" @click="view = 'login'">Sign in</button>
          </div>

        </div>
      </div>
    </template>

    <!-- ── COMPANIES (group_id=1) ──────────────────────────────────────── -->
    <template v-if="view === 'companies'">

      <div class="list-header">
        <div class="list-header-row">
          <div class="list-header-title">Companies</div>
          <span v-if="!companiesLoading" class="list-count-pill">{{ companies.length }}</span>
        </div>
      </div>

      <div v-if="companiesLoading" class="list-spinner">
        <f7-preloader :size="36"></f7-preloader>
      </div>

      <div v-else-if="companiesError" style="padding:16px">
        <div class="alert-err">
          <i class="bi bi-exclamation-circle-fill"></i>
          <span>{{ companiesError }}</span>
        </div>
      </div>

      <div v-else-if="!companies.length" class="list-empty">
        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
          <polyline points="9 22 9 12 15 12 15 22"/>
        </svg>
        <p>No companies found</p>
      </div>

      <div v-else class="co-grid">
        <div
          v-for="c in companies"
          :key="c.id"
          class="co-grid-card"
          @click="openCompany(c)"
        >
          <div class="co-grid-logo">
            <img v-if="c.logo_url" :src="imgUrl(c.logo_url)" :alt="c.name" class="co-grid-img" />
            <div v-else class="co-grid-placeholder">
              <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                <polyline points="9 22 9 12 15 12 15 22"/>
              </svg>
            </div>
          </div>
          <div class="co-grid-name">{{ c.name }}</div>
          <div v-if="c.address" class="co-grid-addr">{{ c.address }}</div>
          <div class="co-grid-footer">
            <span class="co-grid-users">
              <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:3px;vertical-align:middle">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
              </svg>
              {{ c.users?.length ?? 0 }}
            </span>
            <span :class="['co-grid-status', c.is_active ? 'co-grid-active' : 'co-grid-inactive']">
              {{ c.is_active ? 'Active' : 'Inactive' }}
            </span>
          </div>
        </div>
      </div>

    </template>

    <!-- ── USERS (admin) ─────────────────────────────────────────────── -->
    <template v-if="view === 'users'">

      <div class="list-header">
        <div class="list-header-row">
          <div class="list-header-title">{{ selectedCompany ? selectedCompany.name : 'Staff' }}</div>
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
            <div class="user-avatar-wrap">
              <div class="user-avatar">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                  <circle cx="12" cy="7" r="4"/>
                </svg>
              </div>
              <span class="user-qt-badge">{{ u.quotations_count ?? 0 }}</span>
            </div>
            <div class="qt-card-left">
              <div class="qt-number">{{ u.full_name || u.username }}</div>
              <div class="qt-customer">{{ u.username }} · {{ u.email }}</div>
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
            <button class="qt-call-btn" @click.stop="startConversation(u)">
              <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:3px">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
              </svg>
              Message
            </button>
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
          <div class="user-avatar" style="width:44px;height:44px;background:rgba(30,125,30,.25)">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1e7d1e" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
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
          <div class="user-avatar" style="width:44px;height:44px;background:rgba(30,125,30,.25)">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#1e7d1e" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
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

        <div style="display:flex;gap:10px">
          <div class="lf-group" style="flex:1">
            <label class="lf-label">First Name</label>
            <div class="lf-input-wrap">
              <input class="lf-input" type="text" placeholder="First"
                :value="userForm.first_name" @input="userForm.first_name = $event.target.value"
                autocomplete="off" style="padding-left:12px" />
            </div>
          </div>
          <div class="lf-group" style="flex:1">
            <label class="lf-label">Last Name</label>
            <div class="lf-input-wrap">
              <input class="lf-input" type="text" placeholder="Last"
                :value="userForm.last_name" @input="userForm.last_name = $event.target.value"
                autocomplete="off" style="padding-left:12px" />
            </div>
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
          :disabled="userCreateLoading || !userForm.first_name || !userForm.last_name || !userForm.username || !userForm.email || !userForm.password || !userForm.group_id">
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
          <button v-if="selectedQuote.public_token" class="btn-ghost" @click="openPublicView">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
              <polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/>
            </svg>
            Customer View
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

  <!-- ── CONVERSATIONS ────────────────────────────────────────────────── -->
  <template v-if="view === 'conversations'">

    <div class="list-header">
      <div class="list-header-row">
        <div class="list-header-title">Messages</div>
        <span v-if="!convLoading" class="list-count-pill">{{ conversations.length }}</span>
      </div>
    </div>

    <div v-if="convLoading && !conversations.length" class="list-spinner">
      <f7-preloader :size="36"></f7-preloader>
    </div>

    <div v-else-if="convError" style="padding:16px">
      <div class="alert-err">
        <i class="bi bi-exclamation-circle-fill"></i>
        <span>{{ convError }}</span>
      </div>
    </div>

    <div v-else-if="!conversations.length" class="list-empty">
      <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
      </svg>
      <p>No conversations yet</p>
    </div>

    <!-- Compose FAB -->
    <button class="list-fab" @click="openUserPicker">
      <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
        <line x1="12" y1="8" x2="12" y2="14"/><line x1="9" y1="11" x2="15" y2="11"/>
      </svg>
    </button>

    <!-- User picker overlay -->
    <div v-if="userPickerOpen" class="qp-overlay" @click.self="userPickerOpen = false">
      <div class="qp-sheet">
        <div class="qp-header">
          <span class="qp-title">New Message</span>
          <button class="qp-close" @click="userPickerOpen = false">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>
        <div class="qp-search-wrap">
          <i class="bi bi-search qp-search-icon"></i>
          <input class="qp-search-input" type="text" placeholder="Search by name or username…"
            :value="userPickerSearch" @input="userPickerSearch = $event.target.value" autocomplete="off" />
        </div>
        <div class="qp-list">
          <div v-if="userPickerLoading" style="padding:20px;text-align:center">
            <f7-preloader :size="28"></f7-preloader>
          </div>
          <div v-else-if="!filteredPickerUsers.length" class="qp-empty">No users found</div>
          <div v-else v-for="u in filteredPickerUsers" :key="u.id" class="qp-item" @click="startConversation(u); userPickerOpen = false">
            <div class="qp-item-row">
              <span class="qp-item-num">{{ u.full_name || u.username }}</span>
              <span class="st-badge" style="font-size:.62rem;background:rgba(0,0,0,.07);color:#555">{{ u.group_name }}</span>
            </div>
            <div class="qp-item-meta">@{{ u.username }}{{ u.email ? ' · ' + u.email : '' }}</div>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="conv-list">
      <div
        v-for="c in conversations.filter(c => c.participants?.length)"
        :key="c.id"
        class="conv-item"
        @click="openConversation(c)"
      >
        <div class="conv-avatar-wrap">
          <div class="conv-avatar">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
              <circle cx="12" cy="7" r="4"/>
            </svg>
          </div>
          <span v-if="c.unread_count > 0" class="conv-unread-badge">
            {{ c.unread_count > 99 ? '99+' : c.unread_count }}
          </span>
        </div>
        <div class="conv-content">
          <div class="conv-top-row">
            <span :class="['conv-name', c.unread_count > 0 && 'conv-name-unread']">
              {{ c.participants.map(p => (p.first_name && p.last_name) ? p.first_name + ' ' + p.last_name : p.username).join(', ') }}
            </span>
            <span class="conv-ts">{{ fmtConvTime(c.updated_at) }}</span>
          </div>
          <div :class="['conv-preview', c.unread_count > 0 && 'conv-preview-unread']">
            <template v-if="c.last_message">
              <span class="conv-preview-who">{{ c.last_message.sender_username }}: </span>{{ c.last_message.body }}
            </template>
            <template v-else>No messages yet</template>
          </div>
        </div>
      </div>
    </div>

  </template>

  <!-- ── CHAT ──────────────────────────────────────────────────────────── -->
  <template v-if="view === 'chat'">

    <div class="chat-page-wrap">

      <!-- Load older messages -->
      <div v-if="msgHasMore" class="chat-load-more">
        <button class="chat-load-btn" @click="loadMoreMessages" :disabled="msgLoadingMore">
          <f7-preloader v-if="msgLoadingMore" :size="14" color="gray"></f7-preloader>
          <span v-else>Load older messages</span>
        </button>
      </div>

      <!-- Messages -->
      <div class="chat-messages-area">
        <div v-if="msgLoading && !messages.length" class="list-spinner" style="padding-top:40px">
          <f7-preloader :size="32"></f7-preloader>
        </div>
        <div v-else-if="msgError" style="padding:16px">
          <div class="alert-err">
            <i class="bi bi-exclamation-circle-fill"></i>
            <span>{{ msgError }}</span>
          </div>
        </div>
        <template v-else>
          <div
            v-for="msg in messages"
            :key="msg.id"
            :class="['chat-msg-wrap', msg.sender_id === user.id ? 'chat-mine' : 'chat-theirs']"
          >
            <div v-if="msg.sender_id !== user.id" class="chat-sender-name">{{ msg.sender_username }}</div>
            <div v-if="msg.body" class="chat-bubble">{{ msg.body }}</div>

            <!-- Image -->
            <div v-if="msg.image_url" class="chat-img-msg" @click="openQuoteLink(msg.image_url)">
              <img :src="imgUrl(msg.image_url)" loading="lazy" />
            </div>

            <!-- Quote card -->
            <div v-if="msg.quote" class="chat-quote-card" @click.stop="openQuoteLink(msg.quote.public_url)">
              <div class="cqc-body">
                <div class="cqc-row">
                  <span class="cqc-num">{{ msg.quote.quote_number }}</span>
                  <span :class="['st-badge', 'st-' + msg.quote.status]" style="font-size:.6rem">
                    {{ msg.quote.status.replace('_', ' ') }}
                  </span>
                </div>
                <div class="cqc-customer">{{ msg.quote.customer_name }}</div>
                <div class="cqc-total">R {{ fmt(msg.quote.total) }}</div>
              </div>
              <div class="cqc-view">
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                  <polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/>
                </svg>
                View Quote
              </div>
            </div>

            <div class="chat-time">{{ fmtMsgTime(msg.created_at) }}</div>
          </div>
        </template>
        <!-- anchor for scroll-to-bottom -->
        <div class="chat-bottom-anchor"></div>
      </div>

      <!-- Input bar -->
      <div class="chat-input-bar">
        <!-- Attached quote pill -->
        <div v-if="attachedQuote" class="chat-attach-pill">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="16" y1="13" x2="8" y2="13"/>
            <line x1="16" y1="17" x2="8" y2="17"/>
            <polyline points="10 9 9 9 8 9"/>
          </svg>
          <span>{{ attachedQuote.quote_number }} — {{ attachedQuote.customer_name }}</span>
          <button class="chat-attach-remove" @click="attachedQuote = null">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>
        <!-- Image preview pill -->
        <div v-if="msgImagePreview" class="chat-img-preview">
          <img :src="msgImagePreview" class="chat-img-thumb" />
          <button class="chat-attach-remove" @click="removeChatImage">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>
        <div class="chat-input-row">
          <!-- Hidden file input -->
          <input type="file" accept="image/*" style="display:none" id="chat-img-input" @change="onChatImagePicked" />
          <button class="chat-icon-btn" @click="openQuotePicker" title="Attach existing quote">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/>
            </svg>
          </button>
          <button class="chat-icon-btn" @click="goToCreateFromChat" title="Create new quote">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
              <polyline points="14 2 14 8 20 8"/>
              <line x1="12" y1="18" x2="12" y2="12"/>
              <line x1="9" y1="15" x2="15" y2="15"/>
            </svg>
          </button>
          <label for="chat-img-input" class="chat-icon-btn" title="Send image" style="cursor:pointer;margin:0">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/>
              <polyline points="21 15 16 10 5 21"/>
            </svg>
          </label>
          <input
            class="chat-input"
            type="text"
            placeholder="Message…"
            :value="msgInput"
            @input="msgInput = $event.target.value"
            @keyup.enter="sendMessage"
          />
          <button
            class="chat-send-btn"
            @click="sendMessage"
            :disabled="msgSending || (!msgInput.trim() && !attachedQuote && !msgImage)"
          >
            <f7-preloader v-if="msgSending" :size="16" color="white"></f7-preloader>
            <svg v-else xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
              <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
            </svg>
          </button>
        </div>
      </div>

    </div>

    <!-- Quote picker overlay -->
    <div v-if="quotePickerOpen" class="qp-overlay" @click.self="quotePickerOpen = false">
      <div class="qp-sheet">
        <div class="qp-header">
          <span class="qp-title">Attach Quote</span>
          <button class="qp-close" @click="quotePickerOpen = false">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>
        <div class="qp-search-wrap">
          <i class="bi bi-search qp-search-icon"></i>
          <input
            class="qp-search-input"
            type="text"
            placeholder="Search by quote number or customer…"
            :value="quotePickerSearch"
            @input="quotePickerSearch = $event.target.value"
            autocomplete="off"
          />
        </div>
        <div class="qp-list">
          <div v-if="!filteredPickerQuotes.length" class="qp-empty">
            {{ quotations.length ? 'No quotes matched' : 'No quotes loaded — open Quotations first' }}
          </div>
          <div
            v-for="q in filteredPickerQuotes"
            :key="q.id"
            class="qp-item"
            @click="attachQuote(q)"
          >
            <div class="qp-item-row">
              <span class="qp-item-num">{{ q.quote_number }}</span>
              <span :class="['st-badge', 'st-' + q.status]" style="font-size:.62rem">{{ q.status.replace('_', ' ') }}</span>
            </div>
            <div class="qp-item-meta">{{ q.customer_name }} · R {{ fmt(q.total) }}</div>
          </div>
        </div>
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
import { ref, reactive, computed, onMounted, watch, nextTick } from 'vue';
import Push from '../js/push.js';

const API_BASE = (window.cordova || window.location.protocol === 'file:')
  ? 'http://bulesiadmin.co.za/api'
  : '/api';

export default {
  setup() {

    const f7params = {
      name: 'Demo QT',
      theme: 'md',
      darkMode: true,
      colors: { primary: '#1e7d1e' },
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

    // ── Register state ────────────────────────────────────────────────────
    const regForm            = reactive({ first_name: '', last_name: '', username: '', email: '', password: '', company_id: '' });
    const regLoading         = ref(false);
    const regError           = ref('');
    const regCompanies       = ref([]);
    const regCompaniesLoading = ref(false);
    const showRegPw          = ref(false);
    const createdQuote = ref({});

    // ── Users state (admin) ───────────────────────────────────────────────
    const users             = ref([]);
    const usersLoading      = ref(false);
    const usersError        = ref('');
    const userSearch        = ref('');
    const selectedUser      = ref(null);
    const groups            = ref([]);
    const groupsLoading     = ref(false);
    const companies         = ref([]);
    const companiesLoading  = ref(false);
    const companiesError    = ref('');
    const selectedCompany   = ref(null);
    const showUserPw        = ref(false);
    const userCreateLoading = ref(false);
    const userCreateError   = ref('');
    const userForm          = reactive({ first_name: '', last_name: '', username: '', email: '', password: '', group_id: '', is_active: true });

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
        u.full_name?.toLowerCase().includes(q) ||
        u.first_name?.toLowerCase().includes(q) ||
        u.last_name?.toLowerCase().includes(q) ||
        u.username?.toLowerCase().includes(q) ||
        u.email?.toLowerCase().includes(q) ||
        u.group_name?.toLowerCase().includes(q)
      );
    });

    const isAdmin  = computed(() => user.value?.group_name?.toLowerCase() === 'admin');
    const homeView = computed(() => user.value?.group_id === 1 ? 'companies' : 'list');

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

    // ── Chat state ────────────────────────────────────────────────────────
    const conversations     = ref([]);
    const convLoading       = ref(false);
    const convError         = ref('');
    const selectedConv      = ref(null);
    const messages          = ref([]);
    const msgLoading        = ref(false);
    const msgError          = ref('');
    const msgInput          = ref('');
    const msgSending        = ref(false);
    const msgHasMore        = ref(false);
    const msgLoadingMore    = ref(false);
    const attachedQuote     = ref(null);
    const quotePickerOpen   = ref(false);
    const quotePickerSearch = ref('');
    const msgImage          = ref(null);
    const msgImagePreview   = ref(null);
    let   convPollTimer     = null;
    let   msgPollTimer      = null;
    let   bgUnreadTimer     = null;

    const chatCreateMode    = ref(false);
    const userPickerOpen    = ref(false);
    const userPickerSearch  = ref('');
    const userPickerLoading = ref(false);
    const userPickerList    = ref([]);

    const filteredPickerUsers = computed(() => {
      const q   = userPickerSearch.value.trim().toLowerCase();
      const src = userPickerList.value.filter(u => u.id !== user.value.id);
      if (!q) return src;
      return src.filter(u =>
        u.full_name?.toLowerCase().includes(q) ||
        u.username?.toLowerCase().includes(q) ||
        u.email?.toLowerCase().includes(q) ||
        u.group_name?.toLowerCase().includes(q)
      );
    });

    const totalUnread = computed(() =>
      conversations.value.reduce((s, c) => s + (c.unread_count || 0), 0)
    );

    const filteredPickerQuotes = computed(() => {
      const q   = quotePickerSearch.value.trim().toLowerCase();
      const src = quotations.value;
      const filtered = q
        ? src.filter(x =>
            x.quote_number?.toLowerCase().includes(q) ||
            x.customer_name?.toLowerCase().includes(q)
          )
        : src;
      return filtered.slice(0, 40);
    });

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
    const imgUrl = (url) => {
      if (!url) return null;
      if (window.cordova || window.location.protocol === 'file:') return url;
      try { return new URL(url).pathname; } catch { return url; }
    };

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
        initPush(); 
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

    const fetchCompanies = async () => {
      companiesLoading.value = true;
      companiesError.value   = '';
      try {
        const data = await apiFetch('/companies');
        companies.value = data.data ?? data;
      } catch (err) {
        if (err.response?.status === 401) { logout(); return; }
        companiesError.value = err.response?.data?.error || 'Failed to load companies.';
      } finally {
        companiesLoading.value = false;
      }
    };

    const goToCompanies = () => {
      selectedCompany.value = null;
      view.value = 'companies';
      fetchCompanies();
    };

    const openCompany = (c) => {
      selectedCompany.value = c;
      users.value = c.users || [];
      userSearch.value = '';
      view.value = 'users';
    };

    const goToUserCreate = () => {
      userCreateError.value = '';
      showUserPw.value = false;
      Object.assign(userForm, { first_name: '', last_name: '', username: '', email: '', password: '', group_id: '', is_active: true });
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
            first_name: userForm.first_name.trim(),
            last_name:  userForm.last_name.trim(),
            username:   userForm.username.trim(),
            email:      userForm.email.trim(),
            password:   userForm.password,
            group_id:   userForm.group_id,
            is_active:  userForm.is_active ? 1 : 0,
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
        const params = new URLSearchParams();
        if (userId)             params.set('user_id',    userId);
        const qs   = params.toString();
        const path = `/quotations${qs ? '?' + qs : ''}`;
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
      const coName    = user.value.company_name    || 'Bulise';
      const coLogoUrl = user.value.company_logo_url || '';
      const coAddress = user.value.company_address  || '';
      const coPhone   = user.value.company_phone    || '';
      const coEmail   = user.value.company_email    || '';

      const fmtDate = (iso) => {
        if (!iso) return '';
        const months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        const [y, m, d] = iso.split('-');
        return `${parseInt(d)} ${months[parseInt(m) - 1]} ${y}`;
      };

      const rows = (q.items || []).map((i, n) => `
        <tr>
          <td class="c">${n + 1}</td>
          <td>${i.item_description}</td>
          <td>${i.unit || ''}</td>
          <td class="r">${i.quantity}</td>
          <td class="r">R&nbsp;${fmt(i.unit_price)}</td>
          <td class="r"><strong>R&nbsp;${fmt(i.line_total ?? i.quantity * i.unit_price)}</strong></td>
        </tr>`).join('');

      const coContact = [coPhone, coEmail].filter(Boolean).join(' · ');

      return `<!DOCTYPE html><html lang="en"><head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Quotation ${q.quote_number}</title>
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#1a1a1a;padding:36px 40px;max-width:760px;margin:0 auto}
  /* ── Header ── */
  .hdr{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:28px;padding-bottom:22px;border-bottom:2px solid #e0e0e0}
  .co-brand{display:flex;align-items:flex-start;gap:14px}
  .co-logo{width:58px;height:58px;object-fit:cover;border-radius:8px;border:1px solid #e0e0e0;display:block;flex-shrink:0}
  .co-name{font-size:17px;font-weight:800;color:#111;margin-bottom:3px}
  .co-addr{font-size:11px;color:#666;margin-bottom:2px}
  .co-contact{font-size:11px;color:#666}
  .qt-right{text-align:right}
  .qt-label{font-size:22px;font-weight:900;letter-spacing:.04em;color:#111;margin-bottom:6px}
  .qt-num{font-size:13px;font-weight:700;color:#333}
  .qt-date{font-size:12px;color:#666;margin-top:3px}
  /* ── Bill To ── */
  .bill{margin-bottom:22px}
  .sec-lbl{font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#999;margin-bottom:5px}
  .cust-name{font-size:14px;font-weight:700;color:#111;margin-bottom:2px}
  .cust-meta{font-size:12px;color:#555;line-height:1.6}
  .desc-block{margin-bottom:20px}
  .desc-text{font-size:12px;color:#555}
  /* ── Table ── */
  table{width:100%;border-collapse:collapse;margin-bottom:0}
  table,th,td{border:1px solid #ddd}
  th{background:#f5f5f5;padding:8px 10px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#555;text-align:left}
  td{padding:9px 10px;font-size:13px;vertical-align:middle}
  .r{text-align:right}
  .c{text-align:center}
  .sum-lbl{text-align:right;color:#555;border-right:none}
  .sum-val{text-align:right;font-weight:600;white-space:nowrap}
  .grand-lbl{text-align:right;font-weight:800;font-size:14px;border-right:none}
  .grand-val{text-align:right;font-weight:800;font-size:14px;white-space:nowrap}
  /* ── Notes ── */
  .notes{margin-top:20px;padding:12px 14px;background:#f9f9f9;border-left:3px solid #1e7d1e}
  .notes-lbl{font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#aaa;margin-bottom:5px}
  .notes-body{font-size:12px;color:#444;line-height:1.6}
  /* ── Signature ── */
  .sig-section{margin-top:44px;padding-top:20px;border-top:1px solid #ddd}
  .sig-grid{display:grid;grid-template-columns:1fr 1fr;gap:48px}
  .sig-title{font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#333;margin-bottom:18px}
  .sig-field{display:flex;align-items:baseline;gap:6px;margin-bottom:14px;font-size:12px;color:#555}
  .sig-field span{flex:1;border-bottom:1px solid #aaa}
</style>
</head><body>

<div class="hdr">
  <div class="co-brand">
    ${coLogoUrl ? `<img src="${coLogoUrl}" alt="${coName}" class="co-logo">` : ''}
    <div>
      <div class="co-name">${coName}</div>
      ${coAddress ? `<div class="co-addr">${coAddress}</div>` : ''}
      ${coContact ? `<div class="co-contact">${coContact}</div>` : ''}
    </div>
  </div>
  <div class="qt-right">
    <div class="qt-label">QUOTATION</div>
    <div class="qt-num">${q.quote_number}</div>
    <div class="qt-date"><strong>Date:</strong> ${fmtDate(q.quote_date)}</div>
    ${q.valid_until ? `<div class="qt-date"><strong>Valid until:</strong> ${fmtDate(q.valid_until)}</div>` : ''}
  </div>
</div>

<div class="bill">
  <div class="sec-lbl">Bill To</div>
  <div class="cust-name">${q.customer_name}</div>
  <div class="cust-meta">
    ${q.customer_phone ? `${q.customer_phone}<br>` : ''}
    ${q.customer_email ? `${q.customer_email}` : ''}
  </div>
</div>

${q.description ? `<div class="desc-block"><div class="sec-lbl">Description</div><div class="desc-text">${q.description}</div></div>` : ''}

<table>
  <thead><tr>
    <th style="width:32px">#</th>
    <th>Description</th>
    <th style="width:58px">Unit</th>
    <th class="r" style="width:50px">Qty</th>
    <th class="r" style="width:108px">Unit Price</th>
    <th class="r" style="width:108px">Total</th>
  </tr></thead>
  <tbody>
    ${rows}
    <tr><td class="sum-lbl" colspan="5">Subtotal</td><td class="sum-val">R&nbsp;${fmt(q.subtotal)}</td></tr>
    <tr><td class="sum-lbl" colspan="5">VAT (${q.vat_rate}%)</td><td class="sum-val">R&nbsp;${fmt(q.vat_amount)}</td></tr>
    <tr><td class="grand-lbl" colspan="5">TOTAL</td><td class="grand-val">R&nbsp;${fmt(q.total)}</td></tr>
  </tbody>
</table>

${q.notes ? `<div class="notes"><div class="notes-lbl">Notes</div><div class="notes-body">${q.notes}</div></div>` : ''}

<div class="sig-section">
  <div class="sig-grid">
    <div>
      <div class="sig-title">Authorised Signature</div>
      <div class="sig-field">Name: <span>&nbsp;</span></div>
      <div class="sig-field">Date: <span>&nbsp;</span></div>
    </div>
    <div>
      <div class="sig-title">Customer Signature</div>
      <div class="sig-field">Name: <span>&nbsp;</span></div>
      <div class="sig-field">Date: <span>&nbsp;</span></div>
    </div>
  </div>
</div>

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

    const openPublicView = () => {
      const token = selectedQuote.value.public_token;
      if (!token) return;
      const url = API_BASE.replace(/\/api$/, '') + '/q/' + token;
      if (window.cordova?.InAppBrowser) {
        window.cordova.InAppBrowser.open(url, '_blank', 'location=yes,toolbar=yes,closebuttoncaption=Close');
      } else {
        window.open(url, '_blank');
      }
    };

    // ── Chat helpers ──────────────────────────────────────────────────────
    const fmtConvTime = (dt) => {
      if (!dt) return '';
      const d   = new Date(dt.replace(' ', 'T'));
      const now = new Date();
      const ms  = now - d;
      if (ms < 60000)    return 'just now';
      if (ms < 3600000)  return Math.floor(ms / 60000) + 'm';
      if (ms < 86400000) return Math.floor(ms / 3600000) + 'h';
      const [, m, dd] = dt.split(' ')[0].split('-');
      return `${parseInt(dd)}/${parseInt(m)}`;
    };

    const fmtMsgTime = (dt) => {
      if (!dt) return '';
      const t = (dt.split(' ')[1] || '').split(':');
      return `${t[0]}:${t[1]}`;
    };

    const scrollChatToBottom = () => {
      nextTick(() => {
        const anchor = document.querySelector('.chat-bottom-anchor');
        if (anchor) anchor.scrollIntoView({ behavior: 'smooth', block: 'end' });
      });
    };

    // ── Background unread badge poll (runs while logged in) ──────────────
    const stopBgUnreadPoll = () => {
      if (bgUnreadTimer) { clearInterval(bgUnreadTimer); bgUnreadTimer = null; }
    };
    const startBgUnreadPoll = () => {
      stopBgUnreadPoll();
      fetchConversations(true);
      bgUnreadTimer = setInterval(() => fetchConversations(true), 30000);
    };

    // ── Chat polling ──────────────────────────────────────────────────────
    const stopConvPoll = () => {
      if (convPollTimer) { clearInterval(convPollTimer); convPollTimer = null; }
    };
    const startConvPoll = () => {
      stopConvPoll();
      convPollTimer = setInterval(() => fetchConversations(true), 5000);
    };
    const stopMsgPoll = () => {
      if (msgPollTimer) { clearInterval(msgPollTimer); msgPollTimer = null; }
    };
    const startMsgPoll = (convId) => {
      stopMsgPoll();
      msgPollTimer = setInterval(async () => {
        try {
          const data  = await apiFetch(`/conversations/${convId}/messages?limit=50`);
          const msgs  = data.data ?? data;
          const lastId = messages.value.at(-1)?.id || 0;
          const fresh  = msgs.filter(m => m.id > lastId);
          if (fresh.length) {
            messages.value = [...messages.value, ...fresh];
            scrollChatToBottom();
          }
        } catch (_) {}
      }, 5000);
    };

    // ── Chat API calls ────────────────────────────────────────────────────
    const fetchConversations = async (silent = false) => {
      if (!silent) convLoading.value = true;
      convError.value = '';
      try {
        const data = await apiFetch('/conversations');
        conversations.value = data.data ?? data;
      } catch (err) {
        if (err.response?.status === 401) { logout(); return; }
        if (!silent) convError.value = err.response?.data?.error || 'Failed to load conversations.';
      } finally {
        convLoading.value = false;
      }
    };

    const fetchMessages = async (convId, beforeId = null) => {
      if (beforeId) {
        msgLoadingMore.value = true;
      } else {
        msgLoading.value = true;
      }
      msgError.value = '';
      try {
        let path = `/conversations/${convId}/messages?limit=50`;
        if (beforeId) path += `&before_id=${beforeId}`;
        const data = await apiFetch(path);
        const msgs = data.data ?? data;
        if (beforeId) {
          messages.value = [...msgs, ...messages.value];
        } else {
          messages.value = msgs;
          scrollChatToBottom();
        }
        msgHasMore.value = msgs.length >= 50;
        const idx = conversations.value.findIndex(c => c.id === convId);
        if (idx !== -1) conversations.value[idx].unread_count = 0;
      } catch (err) {
        if (err.response?.status === 401) { logout(); return; }
        msgError.value = err.response?.data?.error || 'Failed to load messages.';
      } finally {
        msgLoading.value     = false;
        msgLoadingMore.value = false;
      }
    };

    const openConversation = async (conv) => {
      selectedConv.value  = conv;
      messages.value      = [];
      msgHasMore.value    = false;
      msgError.value      = '';
      msgInput.value      = '';
      attachedQuote.value = null;
      msgImage.value      = null;
      msgImagePreview.value = null;
      view.value = 'chat';
      await fetchMessages(conv.id);
    };

    const loadMoreMessages = () => {
      if (!selectedConv.value || !messages.value.length || msgLoadingMore.value) return;
      fetchMessages(selectedConv.value.id, messages.value[0].id);
    };

    const sendMessage = async () => {
      const body = msgInput.value.trim();
      if ((!body && !attachedQuote.value && !msgImage.value) || msgSending.value) return;
      msgSending.value = true;
      try {
        let data;
        if (msgImage.value) {
          const fd = new FormData();
          fd.append('image', msgImage.value);
          if (body)                fd.append('body',     body);
          if (attachedQuote.value) fd.append('quote_id', attachedQuote.value.id);
          data = await apiUpload(`/conversations/${selectedConv.value.id}/messages`, fd);
        } else {
          const payload = { body: body || ' ' };
          if (attachedQuote.value) payload.quote_id = attachedQuote.value.id;
          data = await apiFetch(`/conversations/${selectedConv.value.id}/messages`, {
            method: 'POST',
            body: JSON.stringify(payload),
          });
        }
        const msg = data.data ?? data;
        messages.value.push(msg);
        msgInput.value      = '';
        attachedQuote.value = null;
        if (msgImagePreview.value) URL.revokeObjectURL(msgImagePreview.value);
        msgImage.value        = null;
        msgImagePreview.value = null;
        scrollChatToBottom();
        const idx = conversations.value.findIndex(c => c.id === selectedConv.value.id);
        if (idx !== -1) {
          conversations.value[idx].last_message = {
            body: msg.body || '📷 Photo', sender_username: msg.sender_username, created_at: msg.created_at,
          };
          conversations.value[idx].updated_at = msg.created_at;
        }
      } catch (err) {
        if (err.response?.status === 401) { logout(); return; }
      } finally {
        msgSending.value = false;
      }
    };

    const onChatImagePicked = (e) => {
      const file = e.target.files?.[0];
      if (!file) return;
      if (msgImagePreview.value) URL.revokeObjectURL(msgImagePreview.value);
      msgImage.value        = file;
      msgImagePreview.value = URL.createObjectURL(file);
      e.target.value = '';
    };

    const removeChatImage = () => {
      if (msgImagePreview.value) URL.revokeObjectURL(msgImagePreview.value);
      msgImage.value        = null;
      msgImagePreview.value = null;
    };

    const attachQuote = (q) => {
      attachedQuote.value   = q;
      quotePickerOpen.value = false;
      quotePickerSearch.value = '';
    };

    const openQuotePicker = () => {
      if (!quotations.value.length) fetchQuotations();
      quotePickerOpen.value = true;
    };

    const openQuoteLink = (url) => {
      if (!url) return;
      if (window.cordova?.InAppBrowser) {
        window.cordova.InAppBrowser.open(url, '_blank', 'location=yes,toolbar=yes,closebuttoncaption=Close');
      } else {
        window.open(url, '_blank');
      }
    };

    const goToConversations = () => {
      selectedConv.value = null;
      view.value = 'conversations';
      fetchConversations();
    };

    const openUserPicker = async () => {
      userPickerSearch.value = '';
      userPickerOpen.value   = true;
      if (userPickerList.value.length) return;
      userPickerLoading.value = true;
      try {
        const data = await apiFetch('/users');
        userPickerList.value = data.data ?? data;
      } catch (err) {
        if (err.response?.status === 401) logout();
      } finally {
        userPickerLoading.value = false;
      }
    };

    const goToCreateFromChat = () => {
      chatCreateMode.value = true;
      resetForm();
      view.value = 'create';
    };

    const startConversation = async (u) => {
      try {
        const data = await apiFetch('/conversations', {
          method: 'POST',
          body: JSON.stringify({ user_id: u.id }),
        });
        const conv = data.data ?? data;
        // Merge into local list so back-navigation shows it
        const idx = conversations.value.findIndex(c => c.id === conv.id);
        if (idx === -1) conversations.value.unshift(conv);
        await openConversation(conv);
      } catch (err) {
        if (err.response?.status === 401) logout();
      }
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
      if (view.value === 'chat') {
        view.value = 'conversations';
        fetchConversations(true);
        return;
      }
      if (view.value === 'conversations') {
        if (user.value.group_id === 1) goToCompanies();
        else goToList();
        return;
      }
      if (view.value === 'user-password') {
        if (pwReturnView.value === 'users') goToUsers();
        else goToList();
      } else if (view.value === 'user-create') {
        goToUsers();
      } else if (view.value === 'users' && selectedCompany.value) {
        goToCompanies();
      } else if (view.value === 'create' && isEditing.value) {
        cancelEdit();
      } else if (view.value === 'create' && chatCreateMode.value) {
        chatCreateMode.value = false;
        resetForm();
        view.value = 'chat';
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
        if (selectedCompany.value) {
          selectedUser.value = null;
          view.value = 'users';
        } else {
          goToUsers();
        }
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
          body: JSON.stringify({
            login:      loginForm.login,
            password:   loginForm.password,
          }),
        });
        localStorage.setItem('qt_token', data.token);
        localStorage.setItem('qt_user',  JSON.stringify(data.user));
        user.value = data.user;
        loginForm.password = '';
        Push.onLogin();   // send any FCM token that arrived before auth
        startBgUnreadPoll();
        if (data.user.group_id === 1) {
          goToCompanies();
        } else {
          goToList();
        }
      } catch (err) {
        loginError.value = err.response?.data?.error || 'Login failed. Check your credentials.';
      } finally {
        loginLoading.value = false;
      }
    };

    const goToRegister = async () => {
      Object.assign(regForm, { first_name: '', last_name: '', username: '', email: '', password: '', company_id: '' });
      regError.value  = '';
      showRegPw.value = false;
      view.value = 'register';
      regCompaniesLoading.value = true;
      try {
        const res  = await fetch(API_BASE + '/companies/public');
        const data = await res.json();
        regCompanies.value = data.data ?? data;
      } catch (_) {
        regError.value = 'Could not load companies. Check your connection.';
      } finally {
        regCompaniesLoading.value = false;
      }
    };

    const doRegister = async () => {
      regError.value   = '';
      regLoading.value = true;
      try {
        const res  = await fetch(API_BASE + '/register', {
          method:  'POST',
          headers: { 'Content-Type': 'application/json' },
          body:    JSON.stringify({
            first_name: regForm.first_name.trim(),
            last_name:  regForm.last_name.trim(),
            username:   regForm.username.trim(),
            email:      regForm.email.trim(),
            password:   regForm.password,
            company_id: parseInt(regForm.company_id),
          }),
        });
        const data = await res.json();
        if (!res.ok) {
          const d = data?.details?.join(', ') || data?.error || 'Registration failed.';
          regError.value = d;
          return;
        }
        localStorage.setItem('qt_token', data.token);
        localStorage.setItem('qt_user',  JSON.stringify(data.user));
        user.value = data.user;
        initPush(true);
        startBgUnreadPoll();
        if (data.user.group_id === 1) goToCompanies();
        else goToList();
      } catch (_) {
        regError.value = 'Registration failed. Check your connection.';
      } finally {
        regLoading.value = false;
      }
    };

    const logout = () => {
      stopBgUnreadPoll();
      stopConvPoll();
      stopMsgPoll();
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
      users.value           = [];
      selectedUser.value    = null;
      userSearch.value      = '';
      companies.value       = [];
      selectedCompany.value = null;
      conversations.value   = [];
      selectedConv.value    = null;
      messages.value        = [];
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

        if (chatCreateMode.value && selectedConv.value) {
          chatCreateMode.value = false;
          view.value = 'chat';
          try {
            const msgData = await apiFetch(`/conversations/${selectedConv.value.id}/messages`, {
              method: 'POST',
              body: JSON.stringify({ body: data.quote_number, quote_id: quoteId }),
            });
            const msg = msgData.data ?? msgData;
            messages.value.push(msg);
            scrollChatToBottom();
          } catch (_) {}
        } else {
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
        }
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

    watch(view, (newView, oldView) => {
      menuOpen.value = false;
      if (oldView === 'conversations') stopConvPoll();
      if (oldView === 'chat')          stopMsgPoll();
      if (newView === 'conversations') startConvPoll();
      if (newView === 'chat' && selectedConv.value) startMsgPoll(selectedConv.value.id);
    });

    // ── Push notifications ────────────────────────────────────────────────
    // alreadyLoggedIn: true when restoring a session so onLogin() fires
    // inside whenReady — after Push._ready is true — not before deviceready.
    const initPush = (alreadyLoggedIn = false) => {
      console.log('Initializing push notifications...',alreadyLoggedIn);
      const whenReady = (cb) => {
        if (window.cordova?.platformId) cb();
        else document.addEventListener('deviceready', cb, { once: true });
      };
      whenReady(() => {
        Push.init(
          apiFetch,
          async (message) => {
            // Foreground: refresh unread badge silently
            if (!message.tap) {
              fetchConversations(true);
              return;
            }

            // Tapped notification — deep-link to conversation or quotation
            const convId = message.conversation_id || message.data?.conversation_id;
            if (convId) {
              const id   = parseInt(convId);
              const conv = conversations.value.find(c => c.id === id);
              if (conv) {
                await openConversation(conv);
              } else {
                try {
                  const data = await apiFetch(`/conversations/${id}`);
                  const c    = data.data ?? data;
                  if (!conversations.value.find(x => x.id === c.id)) {
                    conversations.value.unshift(c);
                  }
                  await openConversation(c);
                } catch (_) {}
              }
              return;
            }

            const qid = message.quotation_id || message.data?.quotation_id;
            if (qid) openQuotation({ id: parseInt(qid) });
          }
        );
        if (alreadyLoggedIn) Push.onLogin();
      });
    };

    // ── Restore session on mount ──────────────────────────────────────────
    onMounted(() => {
      const token  = localStorage.getItem('qt_token');
      const stored = localStorage.getItem('qt_user');
      if (token && stored) {
        user.value = JSON.parse(stored);
        initPush(true);   // deviceready → Push.init → Push.onLogin in sequence
        startBgUnreadPoll();
        if (user.value.group_id === 1) {
          goToCompanies();
        } else {
          goToList();
        }
      } else {
        initPush();       // no session — init only, onLogin called on doLogin
      }
    });

    return {
      f7params,
      view, user, showPw, menuOpen,
      loginLoading, loginError, loginForm, doLogin, logout,
      regForm, regLoading, regError, regCompanies, regCompaniesLoading, showRegPw,
      goToRegister, doRegister,
      quoteLoading, quoteError, qForm, totals, createdQuote,
      submitQuote, addItem, removeItem, resetForm,
      quotations, quotationsLoading, quotationsError, listFilter, statusFilters,
      searchQuery, dateFrom, dateTo, hasActiveFilters, clearFilters, callPhone,
      filteredQuotations, fetchQuotations, setListFilter, goToList, goToCreate,
      selectedQuote, detailLoading, detailError, shareCopied, openQuotation, shareQuotation, openPublicView,
      conversations, convLoading, convError, selectedConv, totalUnread,
      messages, msgLoading, msgError, msgInput, msgSending, msgHasMore, msgLoadingMore,
      attachedQuote, quotePickerOpen, quotePickerSearch, filteredPickerQuotes,
      fetchConversations, openConversation, loadMoreMessages, sendMessage,
      attachQuote, openQuotePicker, openQuoteLink, goToConversations, startConversation,
      msgImage, msgImagePreview, onChatImagePicked, removeChatImage,
      chatCreateMode, goToCreateFromChat,
      userPickerOpen, userPickerSearch, userPickerLoading, filteredPickerUsers, openUserPicker,
      fmtConvTime, fmtMsgTime,
      previewOpen, previewIndex, openPreview, closePreview,
      uploadedImages, removedImageSlots, onImgsSelected, onImgDrop, removeUploadedImage,
      isEditing, editStatus, goToEdit, cancelEdit, submitEdit, goBack,
      lineTotal, recalc, fmt, imgUrl,
      users, usersLoading, usersError, userSearch, filteredUsers,
      selectedUser, isAdmin, homeView, fetchUsers, openUser, goToUsers,
      groups, groupsLoading, showUserPw, userCreateLoading, userCreateError, userForm,
      goToUserCreate, submitUser,
      companies, companiesLoading, companiesError, fetchCompanies,
      selectedCompany, goToCompanies, openCompany,
      passwordUser, pwForm, showNewPw, showConfirmPw, pwLoading, pwError, pwSuccess,
      goToChangePassword, goToMyPassword, submitPassword,
    };
  }
};
</script>
