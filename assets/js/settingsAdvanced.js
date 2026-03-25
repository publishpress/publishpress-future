/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./assets/jsx/workflow-editor-settings/scheduled-steps-cleanup.jsx"
/*!*************************************************************************!*\
  !*** ./assets/jsx/workflow-editor-settings/scheduled-steps-cleanup.jsx ***!
  \*************************************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   ScheduledStepsCleanupSettings: () => (/* binding */ ScheduledStepsCleanupSettings)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t.return && (u = t.return(), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }

var _publishpressFutureSe = publishpressFutureSettingsAdvanced,
  settings = _publishpressFutureSe.settings,
  text = _publishpressFutureSe.text;
var ScheduledStepsCleanupSettings = function ScheduledStepsCleanupSettings() {
  var _useState = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(settings.scheduledStepsCleanupStatus),
    _useState2 = _slicedToArray(_useState, 2),
    cleanupStatus = _useState2[0],
    setCleanupStatus = _useState2[1];
  var _useState3 = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(settings.scheduledStepsCleanupRetention),
    _useState4 = _slicedToArray(_useState3, 2),
    cleanupRetention = _useState4[0],
    setCleanupRetention = _useState4[1];
  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("th", {
    scope: "row"
  }, text.scheduledStepsCleanup), /*#__PURE__*/React.createElement("td", null, /*#__PURE__*/React.createElement("div", {
    className: "pp-settings-field-row"
  }, /*#__PURE__*/React.createElement("input", {
    type: "radio",
    checked: cleanupStatus,
    name: "future-step-schedule-cleanup",
    id: "future-step-schedule-cleanup-enabled",
    onChange: function onChange() {
      return setCleanupStatus(true);
    },
    value: "1"
  }), /*#__PURE__*/React.createElement("label", {
    htmlFor: "future-step-schedule-cleanup-enabled"
  }, text.scheduledStepsCleanupEnable), /*#__PURE__*/React.createElement("p", {
    className: "description offset"
  }, text.scheduledStepsCleanupEnableDesc), cleanupStatus && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "pp-settings-field-row",
    style: {
      marginLeft: '24px',
      marginTop: '12px',
      marginBottom: '12px'
    }
  }, /*#__PURE__*/React.createElement("label", {
    htmlFor: "future-step-schedule-cleanup-retention",
    style: {
      marginRight: '4px'
    }
  }, text.scheduledStepsCleanupRetention), /*#__PURE__*/React.createElement("input", {
    type: "number",
    id: "future-step-schedule-cleanup-retention",
    value: cleanupRetention,
    placeholder: "30",
    name: "future-step-schedule-cleanup-retention",
    style: {
      width: '60px'
    },
    onChange: function onChange(e) {
      return setCleanupRetention(e.target.value);
    }
  }), /*#__PURE__*/React.createElement("span", {
    style: {
      marginLeft: '4px'
    }
  }, text.days), /*#__PURE__*/React.createElement("p", {
    className: "description"
  }, text.scheduledStepsCleanupRetentionDesc)))), /*#__PURE__*/React.createElement("div", {
    className: "pp-settings-field-row"
  }, /*#__PURE__*/React.createElement("input", {
    type: "radio",
    checked: !cleanupStatus,
    name: "future-step-schedule-cleanup",
    id: "future-step-schedule-cleanup-disabled",
    onChange: function onChange() {
      return setCleanupStatus(false);
    },
    value: "0"
  }), /*#__PURE__*/React.createElement("label", {
    htmlFor: "future-step-schedule-cleanup-disabled"
  }, text.scheduledStepsCleanupDisable), /*#__PURE__*/React.createElement("p", {
    className: "description offset"
  }, text.scheduledStepsCleanupDisableDesc))));
};

/***/ },

/***/ "./node_modules/react-dom/client.js"
/*!******************************************!*\
  !*** ./node_modules/react-dom/client.js ***!
  \******************************************/
(__unused_webpack_module, exports, __webpack_require__) {



var m = __webpack_require__(/*! react-dom */ "react-dom");
if (false) // removed by dead control flow
{} else {
  var i = m.__SECRET_INTERNALS_DO_NOT_USE_OR_YOU_WILL_BE_FIRED;
  exports.createRoot = function(c, o) {
    i.usingClientEntryPoint = true;
    try {
      return m.createRoot(c, o);
    } finally {
      i.usingClientEntryPoint = false;
    }
  };
  exports.hydrateRoot = function(c, h, o) {
    i.usingClientEntryPoint = true;
    try {
      return m.hydrateRoot(c, h, o);
    } finally {
      i.usingClientEntryPoint = false;
    }
  };
}


/***/ },

/***/ "@wordpress/element"
/*!*****************************!*\
  !*** external "wp.element" ***!
  \*****************************/
(module) {

module.exports = wp.element;

/***/ },

/***/ "react-dom"
/*!***************************!*\
  !*** external "ReactDOM" ***!
  \***************************/
(module) {

module.exports = ReactDOM;

/***/ }

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
/******/ 		// Check if module exists (development only)
/******/ 		if (__webpack_modules__[moduleId] === undefined) {
/******/ 			var e = new Error("Cannot find module '" + moduleId + "'");
/******/ 			e.code = 'MODULE_NOT_FOUND';
/******/ 			throw e;
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
// This entry needs to be wrapped in an IIFE because it needs to be isolated against other modules in the chunk.
(() => {
/*!******************************************!*\
  !*** ./assets/jsx/settings-advanced.jsx ***!
  \******************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var react_dom_client__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react-dom/client */ "./node_modules/react-dom/client.js");
/* harmony import */ var _workflow_editor_settings_scheduled_steps_cleanup__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./workflow-editor-settings/scheduled-steps-cleanup */ "./assets/jsx/workflow-editor-settings/scheduled-steps-cleanup.jsx");


var _publishpressFutureSe = publishpressFutureSettingsAdvanced,
  settingsTab = _publishpressFutureSe.settingsTab;
if ('advanced' === settingsTab) {
  var scheduledStepsCleanupContainer = document.getElementById('scheduled-steps-cleanup-settings');
  if (scheduledStepsCleanupContainer) {
    var root = (0,react_dom_client__WEBPACK_IMPORTED_MODULE_0__.createRoot)(scheduledStepsCleanupContainer);
    root.render( /*#__PURE__*/React.createElement(_workflow_editor_settings_scheduled_steps_cleanup__WEBPACK_IMPORTED_MODULE_1__.ScheduledStepsCleanupSettings, null));
  }
}
})();

/******/ })()
;
//# sourceMappingURL=settingsAdvanced.js.map