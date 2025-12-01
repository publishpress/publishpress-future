/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "@publishpress/i18n":
/*!************************************!*\
  !*** external "publishpress.i18n" ***!
  \************************************/
/***/ ((module) => {

module.exports = publishpress.i18n;

/***/ }),

/***/ "@wordpress/element":
/*!*****************************!*\
  !*** external "wp.element" ***!
  \*****************************/
/***/ ((module) => {

module.exports = wp.element;

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
/*!***********************************************************!*\
  !*** ./assets/jsx/workflows-list/status-change/index.jsx ***!
  \***********************************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _publishpress_i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @publishpress/i18n */ "@publishpress/i18n");
/* harmony import */ var _publishpress_i18n__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_publishpress_i18n__WEBPACK_IMPORTED_MODULE_1__);


var StatusChangeHandler = function StatusChangeHandler() {
  var handleStatusChangeClick = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function (e) {
    e.preventDefault();
    var form = e.target.closest('form');
    if (form) {
      form.submit();
    }
  }, []);
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(function () {
    var statusLinks = document.querySelectorAll('.pp-future-workflow-status-change');
    statusLinks.forEach(function (link) {
      link.addEventListener('click', handleStatusChangeClick);
    });
    return function () {
      statusLinks.forEach(function (link) {
        link.removeEventListener('click', handleStatusChangeClick);
      });
    };
  }, [handleStatusChangeClick]);
  return null;
};
document.addEventListener('DOMContentLoaded', function () {
  var root = createRoot(document.createElement('div'));
  root.render( /*#__PURE__*/React.createElement(StatusChangeHandler, null));
});
})();

/******/ })()
;
//# sourceMappingURL=workflowsListStatusChange.js.map