/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./assets/jsx/components/FutureActionPanelAfterActionField.jsx":
/*!*********************************************************************!*\
  !*** ./assets/jsx/components/FutureActionPanelAfterActionField.jsx ***!
  \*********************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   FutureActionPanelAfterActionField: () => (/* binding */ FutureActionPanelAfterActionField),
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_0__);
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }

var FutureActionPanelAfterActionField = function FutureActionPanelAfterActionField(_ref) {
  var children = _ref.children;
  return /*#__PURE__*/React.createElement(_wordpress_components__WEBPACK_IMPORTED_MODULE_0__.Fill, {
    name: "FutureActionPanelAfterActionField"
  }, children);
};
var FutureActionPanelAfterActionFieldSlot = function FutureActionPanelAfterActionFieldSlot(props) {
  return /*#__PURE__*/React.createElement(_wordpress_components__WEBPACK_IMPORTED_MODULE_0__.Slot, _extends({
    name: "FutureActionPanelAfterActionField"
  }, props));
};
FutureActionPanelAfterActionField.Slot = FutureActionPanelAfterActionFieldSlot;
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (FutureActionPanelAfterActionField);

/***/ }),

/***/ "@publishpress/i18n":
/*!************************************!*\
  !*** external "publishpress.i18n" ***!
  \************************************/
/***/ ((module) => {

module.exports = publishpress.i18n;

/***/ }),

/***/ "@wordpress/components":
/*!********************************!*\
  !*** external "wp.components" ***!
  \********************************/
/***/ ((module) => {

module.exports = wp.components;

/***/ }),

/***/ "@wordpress/data":
/*!**************************!*\
  !*** external "wp.data" ***!
  \**************************/
/***/ ((module) => {

module.exports = wp.data;

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
/*!********************************************!*\
  !*** ./assets/jsx/legacy-action/index.jsx ***!
  \********************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _publishpress_i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @publishpress/i18n */ "@publishpress/i18n");
/* harmony import */ var _publishpress_i18n__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_publishpress_i18n__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/data */ "@wordpress/data");
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_data__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _components_FutureActionPanelAfterActionField__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../components/FutureActionPanelAfterActionField */ "./assets/jsx/components/FutureActionPanelAfterActionField.jsx");





var _futureWorkflows = futureWorkflows,
  apiUrl = _futureWorkflows.apiUrl,
  nonce = _futureWorkflows.nonce,
  workflows = _futureWorkflows.workflows;
var Fields = function Fields(_ref) {
  var storeName = _ref.storeName;
  var defaultWorkflow = workflows.length > 0 ? workflows[0].value : 0;
  var _useSelect = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_2__.useSelect)(function (select) {
      var classicEditor = select('publishpress-future/future-action');
      var blockEditor = select('core/edit-post');
      var quickEdit = select('publishpress-future/future-action-quick-edit');
      var postId = 0;
      if (classicEditor && classicEditor.getPostId) {
        postId = classicEditor.getPostId();
      } else if (blockEditor && blockEditor.getCurrentPostId) {
        postId = blockEditor.getCurrentPostId();
      } else if (quickEdit && quickEdit.getPostId) {
        postId = quickEdit.getPostId();
      }
      return {
        action: select(storeName).getAction(),
        workflowId: select(storeName).getExtraDataByName('workflowId') || defaultWorkflow,
        postId: postId,
        currentStoreWorkflowId: select(storeName).getExtraDataByName('workflowId')
      };
    }),
    action = _useSelect.action,
    workflowId = _useSelect.workflowId,
    postId = _useSelect.postId,
    currentStoreWorkflowId = _useSelect.currentStoreWorkflowId;
  var _dispatch = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_2__.dispatch)(storeName),
    setExtraDataByName = _dispatch.setExtraDataByName;
  var handleActionChange = function handleActionChange(value) {
    setExtraDataByName('workflowId', value);
  };
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_3__.useEffect)(function () {
    if (postId) {
      try {
        wp.apiFetch({
          url: "".concat(apiUrl, "/post-expiration/").concat(postId),
          headers: {
            'X-WP-Nonce': nonce
          }
        }).then(function (data) {
          setExtraDataByName('workflowId', data.extraData.workflowId);
        });
      } catch (error) {
        console.error(error);
      }
    }
  }, []);
  if (workflowId && workflowId !== currentStoreWorkflowId) {
    setExtraDataByName('workflowId', workflowId);
  }
  return /*#__PURE__*/React.createElement(React.Fragment, null, workflows.length > 0 && action === 'trigger-workflow' && /*#__PURE__*/React.createElement(_wordpress_components__WEBPACK_IMPORTED_MODULE_0__.PanelRow, {
    className: "future-action-panel-content future-action-full-width"
  }, /*#__PURE__*/React.createElement(_wordpress_components__WEBPACK_IMPORTED_MODULE_0__.SelectControl, {
    label: (0,_publishpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Workflow to trigger', 'post-expirator'),
    value: workflowId,
    options: workflows,
    onChange: handleActionChange,
    className: "future-action-workflow"
  }), /*#__PURE__*/React.createElement("input", {
    type: "hidden",
    name: "future_action_pro_workflow",
    value: workflowId
  })), workflows.length === 0 && action === 'trigger-workflow' && /*#__PURE__*/React.createElement(_wordpress_components__WEBPACK_IMPORTED_MODULE_0__.PanelRow, {
    className: "future-action-panel-content future-action-full-width"
  }, /*#__PURE__*/React.createElement("p", null, (0,_publishpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('No compatible workflows available.', 'post-expirator'))));
};
var LegacyActionFields = function LegacyActionFields() {
  return /*#__PURE__*/React.createElement(_components_FutureActionPanelAfterActionField__WEBPACK_IMPORTED_MODULE_4__.FutureActionPanelAfterActionField, null, function (_ref2) {
    var storeName = _ref2.storeName;
    return /*#__PURE__*/React.createElement(Fields, {
      storeName: storeName
    });
  });
};
wp.plugins.registerPlugin('legacy-action-plugin', {
  render: LegacyActionFields,
  scope: 'publishpress-future'
});
})();

/******/ })()
;
//# sourceMappingURL=legacyAction.js.map