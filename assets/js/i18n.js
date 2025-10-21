/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "@wordpress/i18n":
/*!**************************!*\
  !*** external "wp.i18n" ***!
  \**************************/
/***/ ((module) => {

module.exports = wp.i18n;

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
// This entry needs to be wrapped in an IIFE because it needs to be isolated against other modules in the chunk.
(() => {
/*!*****************************!*\
  !*** ./assets/jsx/i18n.jsx ***!
  \*****************************/
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   __: () => (/* binding */ __),
/* harmony export */   _n: () => (/* binding */ _n),
/* harmony export */   _n_noop: () => (/* binding */ _n_noop),
/* harmony export */   _nx: () => (/* binding */ _nx),
/* harmony export */   _nx_noop: () => (/* binding */ _nx_noop),
/* harmony export */   _x: () => (/* binding */ _x),
/* harmony export */   isRTL: () => (/* binding */ isRTL),
/* harmony export */   sprintf: () => (/* binding */ sprintf)
/* harmony export */ });
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__);
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
function _toConsumableArray(r) { return _arrayWithoutHoles(r) || _iterableToArray(r) || _unsupportedIterableToArray(r) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _iterableToArray(r) { if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) return Array.from(r); }
function _arrayWithoutHoles(r) { if (Array.isArray(r)) return _arrayLikeToArray(r); }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }

var data = function (_window$publishpressI, _window$publishpressI2) {
  var free = ((_window$publishpressI = window.publishpressI18nConfig) === null || _window$publishpressI === void 0 ? void 0 : _window$publishpressI.data) || {};
  var pro = ((_window$publishpressI2 = window.publishpressI18nProConfig) === null || _window$publishpressI2 === void 0 ? void 0 : _window$publishpressI2.data) || {};
  var merged = {};
  var domains = new Set([].concat(_toConsumableArray(Object.keys(free.locale_data || {})), _toConsumableArray(Object.keys(pro.locale_data || {}))));
  domains.forEach(function (domain) {
    merged[domain] = _objectSpread(_objectSpread({}, (free.locale_data || {})[domain] || {}), (pro.locale_data || {})[domain] || {});
  });
  return {
    locale_data: merged
  };
}();
var __ = function __(text) {
  var _data$locale_data;
  var domain = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
  if (domain && (_data$locale_data = data.locale_data) !== null && _data$locale_data !== void 0 && (_data$locale_data = _data$locale_data[domain]) !== null && _data$locale_data !== void 0 && _data$locale_data[text] && data.locale_data[domain][text][0]) {
    return data.locale_data[domain][text][0];
  }
  for (var key in data.locale_data) {
    var _data$locale_data$key;
    if ((_data$locale_data$key = data.locale_data[key]) !== null && _data$locale_data$key !== void 0 && _data$locale_data$key[text] && data.locale_data[key][text][0]) {
      return data.locale_data[key][text][0];
    }
  }
  return (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)(text, domain);
};
var sprintf = function sprintf(text) {
  for (var _len = arguments.length, args = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
    args[_key - 1] = arguments[_key];
  }
  return _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.sprintf.apply(void 0, [text].concat(args));
};
var isRTL = function isRTL() {
  return (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.isRTL)();
};
var _n = function _n(single, plural, number) {
  var domain = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : null;
  single = __(single, domain);
  plural = __(plural, domain);
  return (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__._n)(single, plural, number, domain);
};
var _x = function _x(text, context) {
  var domain = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
  text = __(text, domain);
  return (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__._x)(text, context, domain);
};
var _nx = function _nx(single, plural, number, context) {
  var domain = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : null;
  single = __(single, domain);
  plural = __(plural, domain);
  return (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__._nx)(single, plural, number, context, domain);
};
var _n_noop = function _n_noop(single, plural) {
  var domain = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
  single = __(single, domain);
  plural = __(plural, domain);
  return (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__._n_noop)(single, plural, domain);
};
var _nx_noop = function _nx_noop(single, plural, context) {
  var domain = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : null;
  single = __(single, domain);
  plural = __(plural, domain);
  return (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__._nx_noop)(single, plural, context, domain);
};
if (typeof window.publishpress === 'undefined') {
  window.publishpress = {};
}
if (typeof window.publishpress.i18n === 'undefined') {
  window.publishpress.i18n = {};
}
window.publishpress.i18n.__ = __;
window.publishpress.i18n.sprintf = sprintf;
window.publishpress.i18n.isRTL = isRTL;
window.publishpress.i18n._n = _n;
window.publishpress.i18n._x = _x;
window.publishpress.i18n._nx = _nx;
window.publishpress.i18n._n_noop = _n_noop;
window.publishpress.i18n._nx_noop = _nx_noop;
})();

/******/ })()
;
//# sourceMappingURL=i18n.js.map