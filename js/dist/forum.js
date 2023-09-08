/******/ (() => { // webpackBootstrap
/******/ 	// runtime can't be in strict mode because a global variable is assign and maybe created.
/******/ 	var __webpack_modules__ = ({

/***/ "./src/common/extend.ts":
/*!******************************!*\
  !*** ./src/common/extend.ts ***!
  \******************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var flarum_common_extenders__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! flarum/common/extenders */ "flarum/common/extenders");
/* harmony import */ var flarum_common_extenders__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(flarum_common_extenders__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var flarum_common_models_Group__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! flarum/common/models/Group */ "flarum/common/models/Group");
/* harmony import */ var flarum_common_models_Group__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(flarum_common_models_Group__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var flarum_common_models_User__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! flarum/common/models/User */ "flarum/common/models/User");
/* harmony import */ var flarum_common_models_User__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(flarum_common_models_User__WEBPACK_IMPORTED_MODULE_2__);



/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ([new (flarum_common_extenders__WEBPACK_IMPORTED_MODULE_0___default().Model)((flarum_common_models_User__WEBPACK_IMPORTED_MODULE_2___default())) //
.attribute('twoFactorEnabled').attribute('canDisable2FA').attribute('mustEnable2FA').attribute('backupCodesRemaining'), new (flarum_common_extenders__WEBPACK_IMPORTED_MODULE_0___default().Model)((flarum_common_models_Group__WEBPACK_IMPORTED_MODULE_1___default())) //
.attribute('requires2FA')]);

/***/ }),

/***/ "./src/common/index.ts":
/*!*****************************!*\
  !*** ./src/common/index.ts ***!
  \*****************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var flarum_common_app__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! flarum/common/app */ "flarum/common/app");
/* harmony import */ var flarum_common_app__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(flarum_common_app__WEBPACK_IMPORTED_MODULE_0__);

flarum_common_app__WEBPACK_IMPORTED_MODULE_0___default().initializers.add('ianm/twofactor', function () {});

/***/ }),

/***/ "./src/forum/alertTwoFactorAuthentication.js":
/*!***************************************************!*\
  !*** ./src/forum/alertTwoFactorAuthentication.js ***!
  \***************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ alertTwoFactorAuthentication)
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_esm_extends__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/esm/extends */ "./node_modules/@babel/runtime/helpers/esm/extends.js");
/* harmony import */ var _babel_runtime_helpers_esm_inheritsLoose__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/esm/inheritsLoose */ "./node_modules/@babel/runtime/helpers/esm/inheritsLoose.js");
/* harmony import */ var flarum_common_components_Alert__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! flarum/common/components/Alert */ "flarum/common/components/Alert");
/* harmony import */ var flarum_common_components_Alert__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(flarum_common_components_Alert__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var flarum_common_components_Button__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! flarum/common/components/Button */ "flarum/common/components/Button");
/* harmony import */ var flarum_common_components_Button__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(flarum_common_components_Button__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var flarum_common_helpers_icon__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! flarum/common/helpers/icon */ "flarum/common/helpers/icon");
/* harmony import */ var flarum_common_helpers_icon__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(flarum_common_helpers_icon__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var flarum_common_Component__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! flarum/common/Component */ "flarum/common/Component");
/* harmony import */ var flarum_common_Component__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(flarum_common_Component__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var _components_TwoFactorEnableModal__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./components/TwoFactorEnableModal */ "./src/forum/components/TwoFactorEnableModal.js");








/**
 * Shows an alert if the user has not enabled 2FA.
 *
 * @param {import('../ForumApplication').default} app
 */
function alertTwoFactorAuthentication(app) {
  var user = app.session.user;
  if (!user || !user.mustEnable2FA()) return;
  var Enable2FAButton = /*#__PURE__*/function (_Component) {
    (0,_babel_runtime_helpers_esm_inheritsLoose__WEBPACK_IMPORTED_MODULE_1__["default"])(Enable2FAButton, _Component);
    function Enable2FAButton() {
      return _Component.apply(this, arguments) || this;
    }
    var _proto = Enable2FAButton.prototype;
    _proto.view = function view() {
      return m((flarum_common_components_Button__WEBPACK_IMPORTED_MODULE_3___default()), {
        className: "Button Button--link",
        onclick: this.onclick.bind(this),
        icon: "fas fa-shield-alt"
      }, app.translator.trans('ianm-twofactor.forum.security.enable_2fa_button'));
    };
    _proto.onclick = function onclick() {
      app.modal.show(_components_TwoFactorEnableModal__WEBPACK_IMPORTED_MODULE_6__["default"], {
        user: user
      });
    };
    return Enable2FAButton;
  }((flarum_common_Component__WEBPACK_IMPORTED_MODULE_5___default()));
  var ContainedAlert = /*#__PURE__*/function (_Alert) {
    (0,_babel_runtime_helpers_esm_inheritsLoose__WEBPACK_IMPORTED_MODULE_1__["default"])(ContainedAlert, _Alert);
    function ContainedAlert() {
      return _Alert.apply(this, arguments) || this;
    }
    var _proto2 = ContainedAlert.prototype;
    _proto2.view = function view(vnode) {
      var vdom = _Alert.prototype.view.call(this, vnode);
      return (0,_babel_runtime_helpers_esm_extends__WEBPACK_IMPORTED_MODULE_0__["default"])({}, vdom, {
        children: [m("div", {
          className: "container"
        }, vdom.children)]
      });
    };
    return ContainedAlert;
  }((flarum_common_components_Alert__WEBPACK_IMPORTED_MODULE_2___default()));
  m.mount($('<div className="App-notices"/>').insertBefore('#content')[0], {
    view: function view() {
      return m(ContainedAlert, {
        dismissible: false,
        controls: [m(Enable2FAButton, null)],
        className: "Alert--2faEnable"
      }, app.translator.trans('ianm-twofactor.forum.user_2fa.alert_message'));
    }
  });
}

/***/ }),

/***/ "./src/forum/components/TwoFactorDisableConfirmModal .js":
/*!***************************************************************!*\
  !*** ./src/forum/components/TwoFactorDisableConfirmModal .js ***!
  \***************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ TwoFactorDisableConfirmModal)
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_esm_inheritsLoose__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/esm/inheritsLoose */ "./node_modules/@babel/runtime/helpers/esm/inheritsLoose.js");
/* harmony import */ var flarum_forum_app__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! flarum/forum/app */ "flarum/forum/app");
/* harmony import */ var flarum_forum_app__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(flarum_forum_app__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var flarum_common_components_Modal__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! flarum/common/components/Modal */ "flarum/common/components/Modal");
/* harmony import */ var flarum_common_components_Modal__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(flarum_common_components_Modal__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var flarum_common_components_Button__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! flarum/common/components/Button */ "flarum/common/components/Button");
/* harmony import */ var flarum_common_components_Button__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(flarum_common_components_Button__WEBPACK_IMPORTED_MODULE_3__);




var TwoFactorDisableConfirmModal = /*#__PURE__*/function (_Modal) {
  (0,_babel_runtime_helpers_esm_inheritsLoose__WEBPACK_IMPORTED_MODULE_0__["default"])(TwoFactorDisableConfirmModal, _Modal);
  function TwoFactorDisableConfirmModal() {
    return _Modal.apply(this, arguments) || this;
  }
  var _proto = TwoFactorDisableConfirmModal.prototype;
  _proto.oninit = function oninit(vnode) {
    _Modal.prototype.oninit.call(this, vnode);
    this.loading = false;
  };
  _proto.className = function className() {
    return 'TwoFactorDisableConfirmModal Modal--small';
  };
  _proto.title = function title() {
    return flarum_forum_app__WEBPACK_IMPORTED_MODULE_1___default().translator.trans('ianm-twofactor.forum.security.confirm_disable_2fa_title');
  };
  _proto.content = function content() {
    return m("div", {
      className: "Modal-body"
    }, m("p", null, flarum_forum_app__WEBPACK_IMPORTED_MODULE_1___default().translator.trans('ianm-twofactor.forum.security.confirm_disable_2fa_text')), m("div", {
      className: "Form-group"
    }, m((flarum_common_components_Button__WEBPACK_IMPORTED_MODULE_3___default()), {
      className: "Button Button--danger",
      onclick: this.disable.bind(this),
      loading: this.loading
    }, flarum_forum_app__WEBPACK_IMPORTED_MODULE_1___default().translator.trans('ianm-twofactor.forum.security.disable_2fa_button')), m((flarum_common_components_Button__WEBPACK_IMPORTED_MODULE_3___default()), {
      className: "Button Button--cancel",
      onclick: this.hide.bind(this)
    }, flarum_forum_app__WEBPACK_IMPORTED_MODULE_1___default().translator.trans('ianm-twofactor.forum.security.cancel_button'))));
  };
  _proto.disable = function disable() {
    var _this = this;
    this.loading = true;
    var userId = this.attrs.user.id();
    flarum_forum_app__WEBPACK_IMPORTED_MODULE_1___default().request({
      method: 'DELETE',
      url: flarum_forum_app__WEBPACK_IMPORTED_MODULE_1___default().forum.attribute('apiUrl') + ("/users/" + userId + "/twofactor/disable")
    }).then(function () {
      _this.loading = false;
      _this.attrs.onDisabled();
      _this.hide();
    })["catch"](function (error) {
      // Handle any errors.
    });
  };
  return TwoFactorDisableConfirmModal;
}((flarum_common_components_Modal__WEBPACK_IMPORTED_MODULE_2___default()));


/***/ }),

/***/ "./src/forum/components/TwoFactorEnableModal.js":
/*!******************************************************!*\
  !*** ./src/forum/components/TwoFactorEnableModal.js ***!
  \******************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ TwoFactorEnableModal)
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_esm_inheritsLoose__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/esm/inheritsLoose */ "./node_modules/@babel/runtime/helpers/esm/inheritsLoose.js");
/* harmony import */ var flarum_forum_app__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! flarum/forum/app */ "flarum/forum/app");
/* harmony import */ var flarum_forum_app__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(flarum_forum_app__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var flarum_common_components_Modal__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! flarum/common/components/Modal */ "flarum/common/components/Modal");
/* harmony import */ var flarum_common_components_Modal__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(flarum_common_components_Modal__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var flarum_common_components_Button__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! flarum/common/components/Button */ "flarum/common/components/Button");
/* harmony import */ var flarum_common_components_Button__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(flarum_common_components_Button__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var flarum_common_utils_Stream__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! flarum/common/utils/Stream */ "flarum/common/utils/Stream");
/* harmony import */ var flarum_common_utils_Stream__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(flarum_common_utils_Stream__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var flarum_common_components_LoadingIndicator__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! flarum/common/components/LoadingIndicator */ "flarum/common/components/LoadingIndicator");
/* harmony import */ var flarum_common_components_LoadingIndicator__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(flarum_common_components_LoadingIndicator__WEBPACK_IMPORTED_MODULE_5__);






var TwoFactorEnableModal = /*#__PURE__*/function (_Modal) {
  (0,_babel_runtime_helpers_esm_inheritsLoose__WEBPACK_IMPORTED_MODULE_0__["default"])(TwoFactorEnableModal, _Modal);
  function TwoFactorEnableModal() {
    return _Modal.apply(this, arguments) || this;
  }
  var _proto = TwoFactorEnableModal.prototype;
  _proto.oninit = function oninit(vnode) {
    _Modal.prototype.oninit.call(this, vnode);
    this.user = this.attrs.user;
    // Statuses: 'loading', 'displayQR', 'displayBackupCodes', 'final'
    this.status = 'loading';
    this.qrCodeUrl = null;
    this.backupCodes = [];
    this.token = flarum_common_utils_Stream__WEBPACK_IMPORTED_MODULE_4___default()('');
    this.code = null;
    this.activeTab = 'qrcode';
  };
  _proto.className = function className() {
    return 'TwoFactorEnableModal Modal--small';
  };
  _proto.title = function title() {
    return flarum_forum_app__WEBPACK_IMPORTED_MODULE_1___default().translator.trans('ianm-twofactor.forum.security.two_factor_heading');
  };
  _proto.oncreate = function oncreate(vnode) {
    var _this = this;
    _Modal.prototype.oncreate.call(this, vnode);
    var userId = this.user.id();
    flarum_forum_app__WEBPACK_IMPORTED_MODULE_1___default().request({
      method: 'GET',
      url: flarum_forum_app__WEBPACK_IMPORTED_MODULE_1___default().forum.attribute('apiUrl') + ("/users/" + userId + "/twofactor/qrcode")
    }).then(function (response) {
      _this.qrCodeUrl = response.svg;
      _this.code = response.code;
      _this.status = 'displayQR';
      m.redraw();
    });
  };
  _proto.content = function content() {
    var _this2 = this;
    return m("div", {
      className: "Modal-body"
    }, this.status === 'loading' && m("div", {
      className: "loading"
    }, m((flarum_common_components_LoadingIndicator__WEBPACK_IMPORTED_MODULE_5___default()), null), m("p", null, flarum_forum_app__WEBPACK_IMPORTED_MODULE_1___default().translator.trans('ianm-twofactor.forum.security.loading_qr'))), this.status === 'displayQR' && m("div", null, m("div", {
      className: "tabs"
    }, m((flarum_common_components_Button__WEBPACK_IMPORTED_MODULE_3___default()), {
      className: this.activeTab === 'qrcode' ? 'active' : '',
      onclick: function onclick() {
        _this2.activeTab = 'qrcode';
        m.redraw();
      }
    }, flarum_forum_app__WEBPACK_IMPORTED_MODULE_1___default().translator.trans('ianm-twofactor.forum.security.qr_tab')), m((flarum_common_components_Button__WEBPACK_IMPORTED_MODULE_3___default()), {
      className: this.activeTab === 'manual' ? 'active' : '',
      onclick: function onclick() {
        _this2.activeTab = 'manual';
        m.redraw();
      }
    }, flarum_forum_app__WEBPACK_IMPORTED_MODULE_1___default().translator.trans('ianm-twofactor.forum.security.manual_tab'))), this.activeTab === 'qrcode' && m("div", {
      className: "qrSection"
    }, m("img", {
      src: this.qrCodeUrl,
      alt: flarum_forum_app__WEBPACK_IMPORTED_MODULE_1___default().translator.trans('ianm-twofactor.forum.security.qr_code_alt')
    }), m("p", {
      className: "helpText"
    }, flarum_forum_app__WEBPACK_IMPORTED_MODULE_1___default().translator.trans('ianm-twofactor.forum.security.scan_qr_instruction'))), this.activeTab === 'manual' && m("div", {
      className: "manualEntrySection"
    }, m("code", {
      className: "manualEntryCode"
    }, this.code), m("p", {
      className: "helpText"
    }, flarum_forum_app__WEBPACK_IMPORTED_MODULE_1___default().translator.trans('ianm-twofactor.forum.security.manual_entry_instruction'))), m("div", {
      className: "Form"
    }, m("div", {
      className: "Form-group"
    }, m("input", {
      className: "FormControl",
      bidi: this.token,
      placeholder: flarum_forum_app__WEBPACK_IMPORTED_MODULE_1___default().translator.trans('ianm-twofactor.forum.security.enter_token')
    })), m("div", {
      className: "Form-group"
    }, m((flarum_common_components_Button__WEBPACK_IMPORTED_MODULE_3___default()), {
      className: "Button Button--primary",
      onclick: this.verifyToken.bind(this)
    }, flarum_forum_app__WEBPACK_IMPORTED_MODULE_1___default().translator.trans('ianm-twofactor.forum.security.verify_button'))))), this.status === 'displayBackupCodes' && m("div", null, m("p", null, flarum_forum_app__WEBPACK_IMPORTED_MODULE_1___default().translator.trans('ianm-twofactor.forum.security.backup_codes')), m("ul", null, this.backupCodes.map(function (code) {
      return m("li", null, m("code", null, code));
    })), m("p", null, flarum_forum_app__WEBPACK_IMPORTED_MODULE_1___default().translator.trans('ianm-twofactor.forum.security.backup_codes_instruction')), m((flarum_common_components_Button__WEBPACK_IMPORTED_MODULE_3___default()), {
      className: "Button Button--primary",
      onclick: function onclick() {
        _this2.status = 'final';
        m.redraw();
      }
    }, flarum_forum_app__WEBPACK_IMPORTED_MODULE_1___default().translator.trans('ianm-twofactor.forum.security.saved_backup_codes_button'))), this.status === 'final' && m("div", null, m("p", null, flarum_forum_app__WEBPACK_IMPORTED_MODULE_1___default().translator.trans('ianm-twofactor.forum.security.two_factor_enabled_confirmation')), m((flarum_common_components_Button__WEBPACK_IMPORTED_MODULE_3___default()), {
      className: "Button Button--primary",
      onclick: this.finish.bind(this)
    }, flarum_forum_app__WEBPACK_IMPORTED_MODULE_1___default().translator.trans('ianm-twofactor.forum.security.ok_button'))));
  };
  _proto.verifyToken = function verifyToken() {
    var _this3 = this;
    flarum_forum_app__WEBPACK_IMPORTED_MODULE_1___default().request({
      method: 'POST',
      url: flarum_forum_app__WEBPACK_IMPORTED_MODULE_1___default().forum.attribute('apiUrl') + '/users/twofactor/verify',
      body: {
        token: this.token()
      }
    }).then(function (response) {
      _this3.backupCodes = response.backupCodes;
      _this3.status = 'displayBackupCodes';
      _this3.user.twoFactorEnabled(true);
      m.redraw();
    })["catch"](function (error) {
      alert('Verification failed. Please try again.');
    });
  };
  _proto.finish = function finish() {
    this.attrs.onEnabled();
    this.hide();
  };
  return TwoFactorEnableModal;
}((flarum_common_components_Modal__WEBPACK_IMPORTED_MODULE_2___default()));


/***/ }),

/***/ "./src/forum/components/TwoFactorGrid.js":
/*!***********************************************!*\
  !*** ./src/forum/components/TwoFactorGrid.js ***!
  \***********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ TwoFactorGrid)
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_esm_inheritsLoose__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/esm/inheritsLoose */ "./node_modules/@babel/runtime/helpers/esm/inheritsLoose.js");
/* harmony import */ var flarum_forum_app__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! flarum/forum/app */ "flarum/forum/app");
/* harmony import */ var flarum_forum_app__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(flarum_forum_app__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var flarum_common_Component__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! flarum/common/Component */ "flarum/common/Component");
/* harmony import */ var flarum_common_Component__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(flarum_common_Component__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var flarum_common_components_Button__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! flarum/common/components/Button */ "flarum/common/components/Button");
/* harmony import */ var flarum_common_components_Button__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(flarum_common_components_Button__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var flarum_common_utils_ItemList__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! flarum/common/utils/ItemList */ "flarum/common/utils/ItemList");
/* harmony import */ var flarum_common_utils_ItemList__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(flarum_common_utils_ItemList__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var flarum_common_helpers_listItems__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! flarum/common/helpers/listItems */ "flarum/common/helpers/listItems");
/* harmony import */ var flarum_common_helpers_listItems__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(flarum_common_helpers_listItems__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var _TwoFactorGridItem__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./TwoFactorGridItem */ "./src/forum/components/TwoFactorGridItem.js");
/* harmony import */ var flarum_common_components_Tooltip__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! flarum/common/components/Tooltip */ "flarum/common/components/Tooltip");
/* harmony import */ var flarum_common_components_Tooltip__WEBPACK_IMPORTED_MODULE_7___default = /*#__PURE__*/__webpack_require__.n(flarum_common_components_Tooltip__WEBPACK_IMPORTED_MODULE_7__);
/* harmony import */ var _TwoFactorEnableModal__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./TwoFactorEnableModal */ "./src/forum/components/TwoFactorEnableModal.js");
/* harmony import */ var _TwoFactorDisableConfirmModal___WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! ./TwoFactorDisableConfirmModal  */ "./src/forum/components/TwoFactorDisableConfirmModal .js");










var TwoFactorGrid = /*#__PURE__*/function (_Component) {
  (0,_babel_runtime_helpers_esm_inheritsLoose__WEBPACK_IMPORTED_MODULE_0__["default"])(TwoFactorGrid, _Component);
  function TwoFactorGrid() {
    return _Component.apply(this, arguments) || this;
  }
  var _proto = TwoFactorGrid.prototype;
  _proto.oninit = function oninit(vnode) {
    _Component.prototype.oninit.call(this, vnode);
    this.user = this.attrs.user;
    this.twoFactorEnabled = this.user.twoFactorEnabled();
    this.canDisableTwoFactor = this.user.canDisable2FA();
    this.backupCodesRemaining = this.user.backupCodesRemaining() || 0;
  };
  _proto.view = function view() {
    return m("div", {
      className: "TwoFactorGrid"
    }, m("ul", null, flarum_common_helpers_listItems__WEBPACK_IMPORTED_MODULE_5___default()(this.twoFactorItems().toArray())));
  };
  _proto.twoFactorItems = function twoFactorItems() {
    var items = new (flarum_common_utils_ItemList__WEBPACK_IMPORTED_MODULE_4___default())();
    var disableAction = this.getDisableAction();
    var enableAction = m((flarum_common_components_Button__WEBPACK_IMPORTED_MODULE_3___default()), {
      className: "Button Button--primary",
      onclick: this.enableTwoFactor.bind(this)
    }, flarum_forum_app__WEBPACK_IMPORTED_MODULE_1___default().translator.trans('ianm-twofactor.forum.security.enable_2fa_button'));
    items.add('status', m(_TwoFactorGridItem__WEBPACK_IMPORTED_MODULE_6__["default"], {
      icon: "fas fa-shield-alt",
      title: flarum_forum_app__WEBPACK_IMPORTED_MODULE_1___default().translator.trans('ianm-twofactor.forum.security.two_factor_title'),
      value: this.twoFactorEnabled ? flarum_forum_app__WEBPACK_IMPORTED_MODULE_1___default().translator.trans('ianm-twofactor.forum.security.two_factor_enabled') : flarum_forum_app__WEBPACK_IMPORTED_MODULE_1___default().translator.trans('ianm-twofactor.forum.security.two_factor_disabled'),
      action: this.twoFactorEnabled ? disableAction : enableAction,
      helpText: !this.canDisableTwoFactor && flarum_forum_app__WEBPACK_IMPORTED_MODULE_1___default().translator.trans('ianm-twofactor.forum.security.cannot_disable')
    }));

    // Only continue to add other items if Two Factor Authentication is enabled
    if (!this.twoFactorEnabled) return items;
    items.add('backupCodes', m(_TwoFactorGridItem__WEBPACK_IMPORTED_MODULE_6__["default"], {
      icon: "fas fa-key",
      title: "Backup Codes Remaining:",
      value: this.backupCodesRemaining
      // action={
      //   this.backupCodesRemaining < 2 ? (
      //     <Button className="Button Button--primary" onclick={this.generateBackupCodes.bind(this)}>
      //       Generate More
      //     </Button>
      //   ) : null
      // }
    }));

    // Add other items as needed

    return items;
  };
  _proto.getDisableAction = function getDisableAction() {
    var disableButton = m((flarum_common_components_Button__WEBPACK_IMPORTED_MODULE_3___default()), {
      className: "Button Button--danger",
      onclick: this.disableTwoFactor.bind(this),
      disabled: !this.canDisableTwoFactor
    }, flarum_forum_app__WEBPACK_IMPORTED_MODULE_1___default().translator.trans('ianm-twofactor.forum.security.disable_2fa_button'));
    return !this.canDisableTwoFactor ? m((flarum_common_components_Tooltip__WEBPACK_IMPORTED_MODULE_7___default()), {
      text: flarum_forum_app__WEBPACK_IMPORTED_MODULE_1___default().translator.trans('ianm-twofactor.forum.security.cannot_disable_tooltip')
    }, disableButton) : disableButton;
  };
  _proto.enableTwoFactor = function enableTwoFactor() {
    flarum_forum_app__WEBPACK_IMPORTED_MODULE_1___default().modal.show(_TwoFactorEnableModal__WEBPACK_IMPORTED_MODULE_8__["default"], {
      onEnabled: this.onTwoFactorEnabled.bind(this),
      user: this.user
    });
  };
  _proto.onTwoFactorEnabled = function onTwoFactorEnabled() {
    this.twoFactorEnabled = true;
    m.redraw();
  };
  _proto.disableTwoFactor = function disableTwoFactor() {
    flarum_forum_app__WEBPACK_IMPORTED_MODULE_1___default().modal.show(_TwoFactorDisableConfirmModal___WEBPACK_IMPORTED_MODULE_9__["default"], {
      onDisabled: this.onTwoFactorDisabled.bind(this),
      user: this.user
    });
  };
  _proto.onTwoFactorDisabled = function onTwoFactorDisabled() {
    this.twoFactorEnabled = false;
    m.redraw();
  };
  _proto.generateBackupCodes = function generateBackupCodes() {
    // Logic to generate more backup codes
    // Update this.backupCodesRemaining accordingly
    m.redraw();
  };
  return TwoFactorGrid;
}((flarum_common_Component__WEBPACK_IMPORTED_MODULE_2___default()));


/***/ }),

/***/ "./src/forum/components/TwoFactorGridItem.js":
/*!***************************************************!*\
  !*** ./src/forum/components/TwoFactorGridItem.js ***!
  \***************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ TwoFactorGridItem)
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_esm_inheritsLoose__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/esm/inheritsLoose */ "./node_modules/@babel/runtime/helpers/esm/inheritsLoose.js");
/* harmony import */ var flarum_common_Component__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! flarum/common/Component */ "flarum/common/Component");
/* harmony import */ var flarum_common_Component__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(flarum_common_Component__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var flarum_common_helpers_icon__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! flarum/common/helpers/icon */ "flarum/common/helpers/icon");
/* harmony import */ var flarum_common_helpers_icon__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(flarum_common_helpers_icon__WEBPACK_IMPORTED_MODULE_2__);



var TwoFactorGridItem = /*#__PURE__*/function (_Component) {
  (0,_babel_runtime_helpers_esm_inheritsLoose__WEBPACK_IMPORTED_MODULE_0__["default"])(TwoFactorGridItem, _Component);
  function TwoFactorGridItem() {
    return _Component.apply(this, arguments) || this;
  }
  var _proto = TwoFactorGridItem.prototype;
  _proto.view = function view() {
    var _this$attrs = this.attrs,
      iconName = _this$attrs.icon,
      title = _this$attrs.title,
      value = _this$attrs.value,
      action = _this$attrs.action,
      helpText = _this$attrs.helpText;
    return m("li", {
      className: "TwoFactorGrid-item"
    }, m("span", {
      className: "TwoFactorGrid-icon"
    }, flarum_common_helpers_icon__WEBPACK_IMPORTED_MODULE_2___default()(iconName)), m("div", {
      className: "TwoFactorGrid-content"
    }, m("span", {
      className: "TwoFactorGrid-title"
    }, title), m("span", {
      className: "TwoFactorGrid-value"
    }, value), helpText && m("span", {
      className: "helpText TwoFactorGrid-helpText"
    }, helpText)), action && m("span", {
      className: "TwoFactorGrid-actions"
    }, action));
  };
  return TwoFactorGridItem;
}((flarum_common_Component__WEBPACK_IMPORTED_MODULE_1___default()));


/***/ }),

/***/ "./src/forum/components/TwoFactorSettings.js":
/*!***************************************************!*\
  !*** ./src/forum/components/TwoFactorSettings.js ***!
  \***************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ TwoFactorSettings)
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_esm_inheritsLoose__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/esm/inheritsLoose */ "./node_modules/@babel/runtime/helpers/esm/inheritsLoose.js");
/* harmony import */ var flarum_forum_app__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! flarum/forum/app */ "flarum/forum/app");
/* harmony import */ var flarum_forum_app__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(flarum_forum_app__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var flarum_common_Component__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! flarum/common/Component */ "flarum/common/Component");
/* harmony import */ var flarum_common_Component__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(flarum_common_Component__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var flarum_common_components_Button__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! flarum/common/components/Button */ "flarum/common/components/Button");
/* harmony import */ var flarum_common_components_Button__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(flarum_common_components_Button__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _TwoFactorEnableModal__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./TwoFactorEnableModal */ "./src/forum/components/TwoFactorEnableModal.js");
/* harmony import */ var _TwoFactorDisableConfirmModal___WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./TwoFactorDisableConfirmModal  */ "./src/forum/components/TwoFactorDisableConfirmModal .js");
/* harmony import */ var _TwoFactorGrid__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./TwoFactorGrid */ "./src/forum/components/TwoFactorGrid.js");







var TwoFactorSettings = /*#__PURE__*/function (_Component) {
  (0,_babel_runtime_helpers_esm_inheritsLoose__WEBPACK_IMPORTED_MODULE_0__["default"])(TwoFactorSettings, _Component);
  function TwoFactorSettings() {
    return _Component.apply(this, arguments) || this;
  }
  var _proto = TwoFactorSettings.prototype;
  _proto.oninit = function oninit(vnode) {
    _Component.prototype.oninit.call(this, vnode);
    this.twoFactorEnabled = this.attrs.user.twoFactorEnabled();
    this.canDisableTwoFactor = this.attrs.user.canDisable2FA();
    this.loading = false;
  };
  _proto.view = function view() {
    return (
      // <div className="TwoFactorSettings">
      //   <div className="Form-group">
      //     {this.twoFactorEnabled ? (
      //       <div>
      //         <p>{app.translator.trans('ianm-twofactor.forum.security.two_factor_enabled')}</p>
      //         <Button className="Button Button--danger" onclick={this.disableTwoFactor.bind(this)} disabled={!this.canDisableTwoFactor}>
      //           {app.translator.trans('ianm-twofactor.forum.security.disable_2fa_button')}
      //         </Button>
      //         {!this.canDisableTwoFactor && <p className="helpText">{app.translator.trans('ianm-twofactor.forum.security.cannot_disable')}</p>}
      //       </div>
      //     ) : (
      //       <div>
      //         <p>{app.translator.trans('ianm-twofactor.forum.security.two_factor_disabled')}</p>
      //         <Button className="Button Button--primary Button--2fa" onclick={this.enableTwoFactor.bind(this)} icon="fas fa-shield-alt">
      //           {app.translator.trans('ianm-twofactor.forum.security.enable_2fa_button')}
      //         </Button>
      //       </div>
      //     )}
      //   </div>
      // </div>,
      m("div", null, m(_TwoFactorGrid__WEBPACK_IMPORTED_MODULE_6__["default"], {
        user: this.attrs.user
      }))
    );
  };
  return TwoFactorSettings;
}((flarum_common_Component__WEBPACK_IMPORTED_MODULE_2___default()));


/***/ }),

/***/ "./src/forum/extend.ts":
/*!*****************************!*\
  !*** ./src/forum/extend.ts ***!
  \*****************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _common_extend__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../common/extend */ "./src/common/extend.ts");

/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ([].concat(_common_extend__WEBPACK_IMPORTED_MODULE_0__["default"]));

/***/ }),

/***/ "./src/forum/extendForumApplication.js":
/*!*********************************************!*\
  !*** ./src/forum/extendForumApplication.js ***!
  \*********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ extendForumApplication)
/* harmony export */ });
/* harmony import */ var flarum_forum_app__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! flarum/forum/app */ "flarum/forum/app");
/* harmony import */ var flarum_forum_app__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(flarum_forum_app__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var flarum_common_extend__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! flarum/common/extend */ "flarum/common/extend");
/* harmony import */ var flarum_common_extend__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(flarum_common_extend__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var flarum_forum_ForumApplication__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! flarum/forum/ForumApplication */ "flarum/forum/ForumApplication");
/* harmony import */ var flarum_forum_ForumApplication__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(flarum_forum_ForumApplication__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _alertTwoFactorAuthentication__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./alertTwoFactorAuthentication */ "./src/forum/alertTwoFactorAuthentication.js");




function extendForumApplication() {
  (0,flarum_common_extend__WEBPACK_IMPORTED_MODULE_1__.extend)((flarum_forum_ForumApplication__WEBPACK_IMPORTED_MODULE_2___default().prototype), 'mount', function () {
    (0,_alertTwoFactorAuthentication__WEBPACK_IMPORTED_MODULE_3__["default"])((flarum_forum_app__WEBPACK_IMPORTED_MODULE_0___default()));
  });
}

/***/ }),

/***/ "./src/forum/extendLogInModal.js":
/*!***************************************!*\
  !*** ./src/forum/extendLogInModal.js ***!
  \***************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ extendLogInModal)
/* harmony export */ });
/* harmony import */ var flarum_forum_app__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! flarum/forum/app */ "flarum/forum/app");
/* harmony import */ var flarum_forum_app__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(flarum_forum_app__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var flarum_common_extend__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! flarum/common/extend */ "flarum/common/extend");
/* harmony import */ var flarum_common_extend__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(flarum_common_extend__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var flarum_forum_components_LogInModal__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! flarum/forum/components/LogInModal */ "flarum/forum/components/LogInModal");
/* harmony import */ var flarum_forum_components_LogInModal__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(flarum_forum_components_LogInModal__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var flarum_common_utils_Stream__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! flarum/common/utils/Stream */ "flarum/common/utils/Stream");
/* harmony import */ var flarum_common_utils_Stream__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(flarum_common_utils_Stream__WEBPACK_IMPORTED_MODULE_3__);




function extendLogInModal() {
  (0,flarum_common_extend__WEBPACK_IMPORTED_MODULE_1__.extend)((flarum_forum_components_LogInModal__WEBPACK_IMPORTED_MODULE_2___default().prototype), 'oninit', function (vnode) {
    // Initialize the 2FA token stream
    this.twoFactorToken = flarum_common_utils_Stream__WEBPACK_IMPORTED_MODULE_3___default()('');
    this.twoFactorRequired = false;
  });
  (0,flarum_common_extend__WEBPACK_IMPORTED_MODULE_1__.extend)((flarum_forum_components_LogInModal__WEBPACK_IMPORTED_MODULE_2___default().prototype), 'fields', function (items) {
    // Add the 2FA input field to the form
    if (this.twoFactorRequired) {
      items.add('twoFactor', m("div", {
        className: "Form-group TwoFactorInput"
      }, m("input", {
        className: "FormControl",
        name: "twoFactorToken",
        type: "text",
        placeholder: flarum_forum_app__WEBPACK_IMPORTED_MODULE_0___default().translator.trans('ianm-twofactor.forum.log_in.two_factor_placeholder'),
        bidi: this.twoFactorToken,
        disabled: this.loading
      })), 19);
      items.remove('identification');
      items.remove('password');
      items.remove('remember');
    }
  });
  (0,flarum_common_extend__WEBPACK_IMPORTED_MODULE_1__.extend)((flarum_forum_components_LogInModal__WEBPACK_IMPORTED_MODULE_2___default().prototype), 'loginParams', function (data) {
    // Add the twoFactorToken to the login params
    data.twoFactorToken = this.twoFactorToken();
    return data;
  });
  (0,flarum_common_extend__WEBPACK_IMPORTED_MODULE_1__.override)((flarum_forum_components_LogInModal__WEBPACK_IMPORTED_MODULE_2___default().prototype), 'onerror', function (original, error) {
    if (error.status === 401) {
      var errors = error.response && error.response.errors;
      var firstErrorDetail = errors && errors[0] && errors[0].detail || '';
      if (firstErrorDetail.includes('two_factor_required')) {
        // If the error indicates that 2FA is required, show the 2FA input field
        this.twoFactorRequired = true;
        error.alert.content = flarum_forum_app__WEBPACK_IMPORTED_MODULE_0___default().translator.trans('ianm-twofactor.forum.log_in.two_factor_required_message');
        this.alertAttrs = error.alert;
      } else {
        // Handle other types of 401 errors here
        error.alert.content = flarum_forum_app__WEBPACK_IMPORTED_MODULE_0___default().translator.trans('core.forum.log_in.invalid_login_message');
        this.alertAttrs = error.alert;
      }
      m.redraw();
      this.onready();
    } else {
      original(error);
    }
  });
}

/***/ }),

/***/ "./src/forum/extendUserSecurityPage.js":
/*!*********************************************!*\
  !*** ./src/forum/extendUserSecurityPage.js ***!
  \*********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ extendUserSecurityPage)
/* harmony export */ });
/* harmony import */ var flarum_forum_app__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! flarum/forum/app */ "flarum/forum/app");
/* harmony import */ var flarum_forum_app__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(flarum_forum_app__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var flarum_common_extend__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! flarum/common/extend */ "flarum/common/extend");
/* harmony import */ var flarum_common_extend__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(flarum_common_extend__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var flarum_forum_components_UserSecurityPage__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! flarum/forum/components/UserSecurityPage */ "flarum/forum/components/UserSecurityPage");
/* harmony import */ var flarum_forum_components_UserSecurityPage__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(flarum_forum_components_UserSecurityPage__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _components_TwoFactorSettings__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./components/TwoFactorSettings */ "./src/forum/components/TwoFactorSettings.js");
/* harmony import */ var flarum_common_components_FieldSet__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! flarum/common/components/FieldSet */ "flarum/common/components/FieldSet");
/* harmony import */ var flarum_common_components_FieldSet__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(flarum_common_components_FieldSet__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var flarum_common_components_LinkButton__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! flarum/common/components/LinkButton */ "flarum/common/components/LinkButton");
/* harmony import */ var flarum_common_components_LinkButton__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(flarum_common_components_LinkButton__WEBPACK_IMPORTED_MODULE_5__);






function extendUserSecurityPage() {
  (0,flarum_common_extend__WEBPACK_IMPORTED_MODULE_1__.extend)((flarum_forum_components_UserSecurityPage__WEBPACK_IMPORTED_MODULE_2___default().prototype), 'settingsItems', function (items) {
    items.add('twoFactor', m((flarum_common_components_FieldSet__WEBPACK_IMPORTED_MODULE_4___default()), {
      label: flarum_forum_app__WEBPACK_IMPORTED_MODULE_0___default().translator.trans('ianm-twofactor.forum.security.two_factor_heading')
    }, m("p", {
      className: "helpText"
    }, flarum_forum_app__WEBPACK_IMPORTED_MODULE_0___default().translator.trans('ianm-twofactor.forum.security.two_factor_help')), m("p", {
      className: "helpText"
    }, flarum_forum_app__WEBPACK_IMPORTED_MODULE_0___default().translator.trans('ianm-twofactor.forum.security.two_factor_apps', {
      google: m((flarum_common_components_LinkButton__WEBPACK_IMPORTED_MODULE_5___default()), {
        external: true,
        href: "https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2",
        target: "_blank",
        rel: "noopener noreferrer"
      }, "Google Authenticator"),
      microsoft: m((flarum_common_components_LinkButton__WEBPACK_IMPORTED_MODULE_5___default()), {
        external: true,
        href: "https://www.microsoft.com/en-us/account/authenticator",
        target: "_blank",
        rel: "noopener noreferrer"
      }, "Microsoft Authenticator"),
      authy: m((flarum_common_components_LinkButton__WEBPACK_IMPORTED_MODULE_5___default()), {
        external: true,
        href: "https://authy.com/download/",
        target: "_blank",
        rel: "noopener noreferrer"
      }, "Authy")
    })), m(_components_TwoFactorSettings__WEBPACK_IMPORTED_MODULE_3__["default"], {
      user: this.user
    })), 100);
  });
}

/***/ }),

/***/ "./src/forum/index.ts":
/*!****************************!*\
  !*** ./src/forum/index.ts ***!
  \****************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   extend: () => (/* reexport safe */ _extend__WEBPACK_IMPORTED_MODULE_4__["default"])
/* harmony export */ });
/* harmony import */ var flarum_forum_app__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! flarum/forum/app */ "flarum/forum/app");
/* harmony import */ var flarum_forum_app__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(flarum_forum_app__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _extendUserSecurityPage__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./extendUserSecurityPage */ "./src/forum/extendUserSecurityPage.js");
/* harmony import */ var _extendLogInModal__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./extendLogInModal */ "./src/forum/extendLogInModal.js");
/* harmony import */ var _extendForumApplication__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./extendForumApplication */ "./src/forum/extendForumApplication.js");
/* harmony import */ var _extend__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./extend */ "./src/forum/extend.ts");





flarum_forum_app__WEBPACK_IMPORTED_MODULE_0___default().initializers.add('ianm/twofactor', function () {
  (0,_extendUserSecurityPage__WEBPACK_IMPORTED_MODULE_1__["default"])();
  (0,_extendLogInModal__WEBPACK_IMPORTED_MODULE_2__["default"])();
  (0,_extendForumApplication__WEBPACK_IMPORTED_MODULE_3__["default"])();
});

/***/ }),

/***/ "flarum/common/Component":
/*!*********************************************************!*\
  !*** external "flarum.core.compat['common/Component']" ***!
  \*********************************************************/
/***/ ((module) => {

"use strict";
module.exports = flarum.core.compat['common/Component'];

/***/ }),

/***/ "flarum/common/app":
/*!***************************************************!*\
  !*** external "flarum.core.compat['common/app']" ***!
  \***************************************************/
/***/ ((module) => {

"use strict";
module.exports = flarum.core.compat['common/app'];

/***/ }),

/***/ "flarum/common/components/Alert":
/*!****************************************************************!*\
  !*** external "flarum.core.compat['common/components/Alert']" ***!
  \****************************************************************/
/***/ ((module) => {

"use strict";
module.exports = flarum.core.compat['common/components/Alert'];

/***/ }),

/***/ "flarum/common/components/Button":
/*!*****************************************************************!*\
  !*** external "flarum.core.compat['common/components/Button']" ***!
  \*****************************************************************/
/***/ ((module) => {

"use strict";
module.exports = flarum.core.compat['common/components/Button'];

/***/ }),

/***/ "flarum/common/components/FieldSet":
/*!*******************************************************************!*\
  !*** external "flarum.core.compat['common/components/FieldSet']" ***!
  \*******************************************************************/
/***/ ((module) => {

"use strict";
module.exports = flarum.core.compat['common/components/FieldSet'];

/***/ }),

/***/ "flarum/common/components/LinkButton":
/*!*********************************************************************!*\
  !*** external "flarum.core.compat['common/components/LinkButton']" ***!
  \*********************************************************************/
/***/ ((module) => {

"use strict";
module.exports = flarum.core.compat['common/components/LinkButton'];

/***/ }),

/***/ "flarum/common/components/LoadingIndicator":
/*!***************************************************************************!*\
  !*** external "flarum.core.compat['common/components/LoadingIndicator']" ***!
  \***************************************************************************/
/***/ ((module) => {

"use strict";
module.exports = flarum.core.compat['common/components/LoadingIndicator'];

/***/ }),

/***/ "flarum/common/components/Modal":
/*!****************************************************************!*\
  !*** external "flarum.core.compat['common/components/Modal']" ***!
  \****************************************************************/
/***/ ((module) => {

"use strict";
module.exports = flarum.core.compat['common/components/Modal'];

/***/ }),

/***/ "flarum/common/components/Tooltip":
/*!******************************************************************!*\
  !*** external "flarum.core.compat['common/components/Tooltip']" ***!
  \******************************************************************/
/***/ ((module) => {

"use strict";
module.exports = flarum.core.compat['common/components/Tooltip'];

/***/ }),

/***/ "flarum/common/extend":
/*!******************************************************!*\
  !*** external "flarum.core.compat['common/extend']" ***!
  \******************************************************/
/***/ ((module) => {

"use strict";
module.exports = flarum.core.compat['common/extend'];

/***/ }),

/***/ "flarum/common/extenders":
/*!*********************************************************!*\
  !*** external "flarum.core.compat['common/extenders']" ***!
  \*********************************************************/
/***/ ((module) => {

"use strict";
module.exports = flarum.core.compat['common/extenders'];

/***/ }),

/***/ "flarum/common/helpers/icon":
/*!************************************************************!*\
  !*** external "flarum.core.compat['common/helpers/icon']" ***!
  \************************************************************/
/***/ ((module) => {

"use strict";
module.exports = flarum.core.compat['common/helpers/icon'];

/***/ }),

/***/ "flarum/common/helpers/listItems":
/*!*****************************************************************!*\
  !*** external "flarum.core.compat['common/helpers/listItems']" ***!
  \*****************************************************************/
/***/ ((module) => {

"use strict";
module.exports = flarum.core.compat['common/helpers/listItems'];

/***/ }),

/***/ "flarum/common/models/Group":
/*!************************************************************!*\
  !*** external "flarum.core.compat['common/models/Group']" ***!
  \************************************************************/
/***/ ((module) => {

"use strict";
module.exports = flarum.core.compat['common/models/Group'];

/***/ }),

/***/ "flarum/common/models/User":
/*!***********************************************************!*\
  !*** external "flarum.core.compat['common/models/User']" ***!
  \***********************************************************/
/***/ ((module) => {

"use strict";
module.exports = flarum.core.compat['common/models/User'];

/***/ }),

/***/ "flarum/common/utils/ItemList":
/*!**************************************************************!*\
  !*** external "flarum.core.compat['common/utils/ItemList']" ***!
  \**************************************************************/
/***/ ((module) => {

"use strict";
module.exports = flarum.core.compat['common/utils/ItemList'];

/***/ }),

/***/ "flarum/common/utils/Stream":
/*!************************************************************!*\
  !*** external "flarum.core.compat['common/utils/Stream']" ***!
  \************************************************************/
/***/ ((module) => {

"use strict";
module.exports = flarum.core.compat['common/utils/Stream'];

/***/ }),

/***/ "flarum/forum/ForumApplication":
/*!***************************************************************!*\
  !*** external "flarum.core.compat['forum/ForumApplication']" ***!
  \***************************************************************/
/***/ ((module) => {

"use strict";
module.exports = flarum.core.compat['forum/ForumApplication'];

/***/ }),

/***/ "flarum/forum/app":
/*!**************************************************!*\
  !*** external "flarum.core.compat['forum/app']" ***!
  \**************************************************/
/***/ ((module) => {

"use strict";
module.exports = flarum.core.compat['forum/app'];

/***/ }),

/***/ "flarum/forum/components/LogInModal":
/*!********************************************************************!*\
  !*** external "flarum.core.compat['forum/components/LogInModal']" ***!
  \********************************************************************/
/***/ ((module) => {

"use strict";
module.exports = flarum.core.compat['forum/components/LogInModal'];

/***/ }),

/***/ "flarum/forum/components/UserSecurityPage":
/*!**************************************************************************!*\
  !*** external "flarum.core.compat['forum/components/UserSecurityPage']" ***!
  \**************************************************************************/
/***/ ((module) => {

"use strict";
module.exports = flarum.core.compat['forum/components/UserSecurityPage'];

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/esm/extends.js":
/*!************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/extends.js ***!
  \************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ _extends)
/* harmony export */ });
function _extends() {
  _extends = Object.assign ? Object.assign.bind() : function (target) {
    for (var i = 1; i < arguments.length; i++) {
      var source = arguments[i];
      for (var key in source) {
        if (Object.prototype.hasOwnProperty.call(source, key)) {
          target[key] = source[key];
        }
      }
    }
    return target;
  };
  return _extends.apply(this, arguments);
}

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/esm/inheritsLoose.js":
/*!******************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/inheritsLoose.js ***!
  \******************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ _inheritsLoose)
/* harmony export */ });
/* harmony import */ var _setPrototypeOf_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./setPrototypeOf.js */ "./node_modules/@babel/runtime/helpers/esm/setPrototypeOf.js");

function _inheritsLoose(subClass, superClass) {
  subClass.prototype = Object.create(superClass.prototype);
  subClass.prototype.constructor = subClass;
  (0,_setPrototypeOf_js__WEBPACK_IMPORTED_MODULE_0__["default"])(subClass, superClass);
}

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/esm/setPrototypeOf.js":
/*!*******************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/setPrototypeOf.js ***!
  \*******************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ _setPrototypeOf)
/* harmony export */ });
function _setPrototypeOf(o, p) {
  _setPrototypeOf = Object.setPrototypeOf ? Object.setPrototypeOf.bind() : function _setPrototypeOf(o, p) {
    o.__proto__ = p;
    return o;
  };
  return _setPrototypeOf(o, p);
}

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be in strict mode.
(() => {
"use strict";
/*!******************!*\
  !*** ./forum.ts ***!
  \******************/
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   extend: () => (/* reexport safe */ _src_forum__WEBPACK_IMPORTED_MODULE_1__.extend)
/* harmony export */ });
/* harmony import */ var _src_common__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./src/common */ "./src/common/index.ts");
/* harmony import */ var _src_forum__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./src/forum */ "./src/forum/index.ts");


})();

module.exports = __webpack_exports__;
/******/ })()
;
//# sourceMappingURL=forum.js.map