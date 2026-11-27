/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./assets/jsx/workflow-editor/constants.jsx"
/*!**************************************************!*\
  !*** ./assets/jsx/workflow-editor/constants.jsx ***!
  \**************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   CUSTOM_EVENT_HANDLES_COUNT_CHANGED: () => (/* binding */ CUSTOM_EVENT_HANDLES_COUNT_CHANGED),
/* harmony export */   EVENT_DROP_NODE: () => (/* binding */ EVENT_DROP_NODE),
/* harmony export */   FEATURE_ADVANCED_SETTINGS: () => (/* binding */ FEATURE_ADVANCED_SETTINGS),
/* harmony export */   FEATURE_CONTROLS: () => (/* binding */ FEATURE_CONTROLS),
/* harmony export */   FEATURE_DEVELOPER_MODE: () => (/* binding */ FEATURE_DEVELOPER_MODE),
/* harmony export */   FEATURE_FULLSCREEN_MODE: () => (/* binding */ FEATURE_FULLSCREEN_MODE),
/* harmony export */   FEATURE_INSERTER: () => (/* binding */ FEATURE_INSERTER),
/* harmony export */   FEATURE_MINI_MAP: () => (/* binding */ FEATURE_MINI_MAP),
/* harmony export */   FEATURE_MOST_USED_NODES: () => (/* binding */ FEATURE_MOST_USED_NODES),
/* harmony export */   FEATURE_REDUCED_UI: () => (/* binding */ FEATURE_REDUCED_UI),
/* harmony export */   FEATURE_SHOW_ICON_LABELS: () => (/* binding */ FEATURE_SHOW_ICON_LABELS),
/* harmony export */   FEATURE_WELCOME_GUIDE: () => (/* binding */ FEATURE_WELCOME_GUIDE),
/* harmony export */   HANDLE_TYPE_SOURCE: () => (/* binding */ HANDLE_TYPE_SOURCE),
/* harmony export */   HANDLE_TYPE_TARGET: () => (/* binding */ HANDLE_TYPE_TARGET),
/* harmony export */   HTML_ELEMENT_ID: () => (/* binding */ HTML_ELEMENT_ID),
/* harmony export */   INSERTER_TAB_ACTIONS: () => (/* binding */ INSERTER_TAB_ACTIONS),
/* harmony export */   INSERTER_TAB_ADVANCED: () => (/* binding */ INSERTER_TAB_ADVANCED),
/* harmony export */   INSERTER_TAB_TRIGGERS: () => (/* binding */ INSERTER_TAB_TRIGGERS),
/* harmony export */   NODE_TYPE_ACTION: () => (/* binding */ NODE_TYPE_ACTION),
/* harmony export */   NODE_TYPE_ADVANCED: () => (/* binding */ NODE_TYPE_ADVANCED),
/* harmony export */   NODE_TYPE_PLACEHOLDER: () => (/* binding */ NODE_TYPE_PLACEHOLDER),
/* harmony export */   NODE_TYPE_TRIGGER: () => (/* binding */ NODE_TYPE_TRIGGER),
/* harmony export */   POST_TYPE: () => (/* binding */ POST_TYPE),
/* harmony export */   SLOT_SCOPE_WORKFLOW_EDITOR: () => (/* binding */ SLOT_SCOPE_WORKFLOW_EDITOR)
/* harmony export */ });
var POST_TYPE = 'ppfuture_workflow';
var HTML_ELEMENT_ID = 'future-workflow-editor';
var FEATURE_FULLSCREEN_MODE = 'fullscreenMode';
var FEATURE_REDUCED_UI = 'reducedUI';
var FEATURE_SHOW_ICON_LABELS = 'showIconLabels';
var FEATURE_INSERTER = 'inserter';
var FEATURE_MOST_USED_NODES = 'mostUsedNodes';
var FEATURE_DEVELOPER_MODE = 'developerMode';
var FEATURE_WELCOME_GUIDE = 'welcomeGuide';
var FEATURE_ADVANCED_SETTINGS = 'advancedSettings';
var FEATURE_MINI_MAP = 'miniMap';
var FEATURE_CONTROLS = 'controls';
var INSERTER_TAB_TRIGGERS = 'triggers';
var INSERTER_TAB_ACTIONS = 'actions';
var INSERTER_TAB_ADVANCED = 'advanced';
var SLOT_SCOPE_WORKFLOW_EDITOR = 'publishpress-future/edit-workflow';
var EVENT_DROP_NODE = 'application/future-workflow-editor-node';
var NODE_TYPE_TRIGGER = 'trigger';
var NODE_TYPE_ACTION = 'action';
var NODE_TYPE_ADVANCED = 'advanced';
var NODE_TYPE_PLACEHOLDER = 'placeholder';
var HANDLE_TYPE_TARGET = 'target';
var HANDLE_TYPE_SOURCE = 'source';
var CUSTOM_EVENT_HANDLES_COUNT_CHANGED = 'publishpress_future_workflow_editor_handles_count_changed';

/***/ },

/***/ "react"
/*!************************!*\
  !*** external "React" ***!
  \************************/
(module) {

module.exports = React;

/***/ },

/***/ "react-dom"
/*!***************************!*\
  !*** external "ReactDOM" ***!
  \***************************/
(module) {

module.exports = ReactDOM;

/***/ },

/***/ "@wordpress/components"
/*!********************************!*\
  !*** external "wp.components" ***!
  \********************************/
(module) {

module.exports = wp.components;

/***/ },

/***/ "@wordpress/data"
/*!**************************!*\
  !*** external "wp.data" ***!
  \**************************/
(module) {

module.exports = wp.data;

/***/ },

/***/ "@wordpress/element"
/*!*****************************!*\
  !*** external "wp.element" ***!
  \*****************************/
(module) {

module.exports = wp.element;

/***/ },

/***/ "@wordpress/hooks"
/*!***************************!*\
  !*** external "wp.hooks" ***!
  \***************************/
(module) {

module.exports = wp.hooks;

/***/ },

/***/ "@wordpress/i18n"
/*!**************************!*\
  !*** external "wp.i18n" ***!
  \**************************/
(module) {

module.exports = wp.i18n;

/***/ },

/***/ "@wordpress/plugins"
/*!*****************************!*\
  !*** external "wp.plugins" ***!
  \*****************************/
(module) {

module.exports = wp.plugins;

/***/ },

/***/ "@wordpress/url"
/*!*************************!*\
  !*** external "wp.url" ***!
  \*************************/
(module) {

module.exports = wp.url;

/***/ }

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	const __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		const cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		const module = __webpack_module_cache__[moduleId] = {
/******/ 			id: moduleId,
/******/ 			loaded: false,
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		if (!(moduleId in __webpack_modules__)) {
/******/ 			delete __webpack_module_cache__[moduleId];
/******/ 			const e = new Error("Cannot find module '" + moduleId + "'");
/******/ 			e.code = 'MODULE_NOT_FOUND';
/******/ 			throw e;
/******/ 		}
/******/ 		__webpack_modules__[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Flag the module as loaded
/******/ 		module.loaded = true;
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = __webpack_modules__;
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/amd define */
/******/ 	__webpack_require__.amdD = function () {
/******/ 		throw new Error('define cannot be used indirect');
/******/ 	};
/******/ 	
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = (module) => {
/******/ 		const getter = module && module.__esModule ?
/******/ 			() => (module['default']) :
/******/ 			() => (module);
/******/ 		__webpack_require__.d(getter, { a: getter });
/******/ 		return getter;
/******/ 	};
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	// define getter/value functions for harmony exports
/******/ 	__webpack_require__.d = (exports, definition) => {
/******/ 		for(var key in definition) {
/******/ 			if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 				Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 			}
/******/ 		}
/******/ 	};
/******/ 	
/******/ 	/* webpack/runtime/ensure chunk */
/******/ 	__webpack_require__.f = {};
/******/ 	// This file contains only the entry chunk.
/******/ 	// The chunk loading function for additional chunks
/******/ 	__webpack_require__.e = (chunkId) => {
/******/ 		const promises = [];
/******/ 		__webpack_require__.f.j(chunkId, promises);
/******/ 		return Promise.all(promises);
/******/ 	};
/******/ 	
/******/ 	/* webpack/runtime/get javascript chunk filename */
/******/ 	// This function allow to reference async chunks
/******/ 	__webpack_require__.u = (chunkId) => (chunkId + ".js");
/******/ 	
/******/ 	/* webpack/runtime/global */
/******/ 	__webpack_require__.g = (function() {
/******/ 		if (typeof globalThis === 'object') return globalThis;
/******/ 		try {
/******/ 			return this || new Function('return this')();
/******/ 		} catch (e) {
/******/ 			if (typeof window === 'object') return window;
/******/ 		}
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop));
/******/ 	
/******/ 	/* webpack/runtime/load script */
/******/ 	(() => {
/******/ 		const inProgress = {};
/******/ 		const dataWebpackPrefix = "post-expirator:";
/******/ 		// loadScript function to load a script via script tag
/******/ 		__webpack_require__.l = (url, done, key, chunkId) => {
/******/ 			if(inProgress[url]) { inProgress[url].push(done); return; }
/******/ 			let script, needAttach;
/******/ 			if(key !== undefined) {
/******/ 				const scripts = document.getElementsByTagName("script");
/******/ 				for(var i = 0; i < scripts.length; i++) {
/******/ 					const s = scripts[i];
/******/ 					if(s.getAttribute("src") == url || s.getAttribute("data-webpack") == dataWebpackPrefix + key) { script = s; break; }
/******/ 				}
/******/ 			}
/******/ 			if(!script) {
/******/ 				needAttach = true;
/******/ 				script = document.createElement('script');
/******/ 		
/******/ 				script.charset = 'utf-8';
/******/ 				if (__webpack_require__.nc) {
/******/ 					script.setAttribute("nonce", __webpack_require__.nc);
/******/ 				}
/******/ 				script.setAttribute("data-webpack", dataWebpackPrefix + key);
/******/ 		
/******/ 				script.src = url;
/******/ 			}
/******/ 			inProgress[url] = [done];
/******/ 			const onScriptComplete = (prev, event) => {
/******/ 				// avoid mem leaks in IE.
/******/ 				script.onerror = script.onload = null;
/******/ 				clearTimeout(timeout);
/******/ 				const doneFns = inProgress[url];
/******/ 				delete inProgress[url];
/******/ 				script.parentNode?.removeChild(script);
/******/ 				doneFns?.forEach((fn) => (fn(event)));
/******/ 				if(prev) return prev(event);
/******/ 			}
/******/ 			const timeout = setTimeout(onScriptComplete.bind(0, 0, { type: 'timeout', target: script }), 120000);
/******/ 			script.onerror = onScriptComplete.bind(0, script.onerror);
/******/ 			script.onload = onScriptComplete.bind(0, script.onload);
/******/ 			needAttach && document.head.appendChild(script);
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = (exports) => {
/******/ 		Object.defineProperties(exports, { [Symbol.toStringTag]: { value: 'Module' }, __esModule: { value: true } });
/******/ 	};
/******/ 	
/******/ 	/* webpack/runtime/node module decorator */
/******/ 	__webpack_require__.nmd = (module) => {
/******/ 		module.paths = [];
/******/ 		if (!module.children) module.children = [];
/******/ 		return module;
/******/ 	};
/******/ 	
/******/ 	/* webpack/runtime/set anonymous default export name */
/******/ 	// set .name for anonymous default exports per ES spec
/******/ 	// skipped when the property is non-configurable (pre-ES2015 engines),
/******/ 	// where Object.defineProperty would throw
/******/ 	__webpack_require__.dn = (x) => {
/******/ 		var descriptor = Object.getOwnPropertyDescriptor(x, "name");
/******/ 		if (!descriptor || (!descriptor.writable && descriptor.configurable)) Object.defineProperty(x, "name", { value: "default", configurable: true });
/******/ 	};
/******/ 	
/******/ 	/* webpack/runtime/publicPath */
/******/ 	(() => {
/******/ 		let scriptUrl;
/******/ 		if (typeof document !== "undefined") {
/******/ 			const currentScript = document.currentScript;
/******/ 			if (!currentScript || /^script$/i.test(currentScript.tagName)) {
/******/ 				scriptUrl = currentScript?.src;
/******/ 				if (!scriptUrl) {
/******/ 					const scripts = document.getElementsByTagName("script");
/******/ 					let i = scripts.length;
/******/ 					while (i && (!scriptUrl || !/^https?:/.test(scriptUrl))) scriptUrl = scripts[--i].src;
/******/ 				}
/******/ 			}
/******/ 		} else if (typeof importScripts !== "undefined") scriptUrl = location + "";
/******/ 		// When supporting browsers where an automatic publicPath is not supported you must specify an output.publicPath manually via configuration
/******/ 		// or pass an empty string ("") and set the __webpack_public_path__ variable from your code to use your own logic.
/******/ 		if (!scriptUrl) throw new Error("Automatic publicPath is not supported in this browser");
/******/ 		__webpack_require__.p = scriptUrl.replace(/^blob:|[?#].*$/g, "").replace(/\/[^/]+$/, "/");
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/jsonp chunk loading */
/******/ 	(() => {
/******/ 		// no baseURI
/******/ 		
/******/ 		// object to store loaded and loading chunks
/******/ 		// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 		// [resolve, reject, Promise] = chunk loading, 0 = chunk loaded
/******/ 		const installedChunks = {
/******/ 			"workflowEditor": 0
/******/ 		};
/******/ 		
/******/ 		__webpack_require__.f.j = (chunkId, promises) => {
/******/ 				// JSONP chunk loading for javascript
/******/ 				let installedChunkData = __webpack_require__.o(installedChunks, chunkId) ? installedChunks[chunkId] : undefined;
/******/ 				if(installedChunkData !== 0) { // 0 means "already installed".
/******/ 		
/******/ 					// a Promise means "currently loading".
/******/ 					if(installedChunkData) {
/******/ 						promises.push(installedChunkData[2]);
/******/ 					} else {
/******/ 						if(true) { // all chunks have JS
/******/ 							// setup Promise in chunk cache
/******/ 							const promise = new Promise((resolve, reject) => (installedChunkData = installedChunks[chunkId] = [resolve, reject]));
/******/ 							promises.push(installedChunkData[2] = promise);
/******/ 		
/******/ 							// create error before stack unwound to get useful stacktrace later
/******/ 							const error = new Error();
/******/ 							const loadingEnded = (event) => {
/******/ 								if(__webpack_require__.o(installedChunks, chunkId)) {
/******/ 									installedChunkData = installedChunks[chunkId];
/******/ 									if(installedChunkData !== 0) installedChunks[chunkId] = undefined;
/******/ 									if(installedChunkData) {
/******/ 										const errorType = event && (event.type === 'load' ? 'missing' : event.type);
/******/ 										const realSrc = event?.target?.src;
/******/ 										error.message = 'Loading chunk ' + chunkId + ' failed.\n(' + errorType + ': ' + realSrc + ')';
/******/ 										error.name = 'ChunkLoadError';
/******/ 										error.type = errorType;
/******/ 										error.request = realSrc;
/******/ 										error.event = event;
/******/ 										installedChunkData[1](error);
/******/ 									}
/******/ 								}
/******/ 							};
/******/ 							__webpack_require__.l(__webpack_require__.p + __webpack_require__.u(chunkId), loadingEnded, "chunk-" + chunkId, chunkId);
/******/ 						}
/******/ 					}
/******/ 				}
/******/ 		};
/******/ 		
/******/ 		// no prefetching
/******/ 		
/******/ 		// no preloaded
/******/ 		
/******/ 		// no HMR
/******/ 		
/******/ 		// no HMR manifest
/******/ 		
/******/ 		// no on chunks loaded
/******/ 		
/******/ 		// install a JSONP callback for chunk loading
/******/ 		const webpackJsonpCallback = (parentChunkLoadingFunction, data) => {
/******/ 			let [chunkIds, moreModules, runtime] = data;
/******/ 			// add "moreModules" to the modules object,
/******/ 			// then flag all "chunkIds" as loaded and fire callback
/******/ 			var moduleId, chunkId, i = 0;
/******/ 			if(chunkIds.some((id) => (installedChunks[id] !== 0))) {
/******/ 				for(moduleId in moreModules) {
/******/ 					if(__webpack_require__.o(moreModules, moduleId)) {
/******/ 						__webpack_require__.m[moduleId] = moreModules[moduleId];
/******/ 					}
/******/ 				}
/******/ 				if(runtime) var result = runtime(__webpack_require__);
/******/ 			}
/******/ 			if(parentChunkLoadingFunction) parentChunkLoadingFunction(data);
/******/ 			for(;i < chunkIds.length; i++) {
/******/ 				chunkId = chunkIds[i];
/******/ 				if(__webpack_require__.o(installedChunks, chunkId) && installedChunks[chunkId]) {
/******/ 					installedChunks[chunkId][0]();
/******/ 				}
/******/ 				installedChunks[chunkId] = 0;
/******/ 			}
/******/ 		
/******/ 		}
/******/ 		
/******/ 		const chunkLoadingGlobal = Array.isArray(self["webpackChunkpost_expirator"]) ? self["webpackChunkpost_expirator"] : (self["webpackChunkpost_expirator"] = []);
/******/ 		chunkLoadingGlobal.forEach(webpackJsonpCallback.bind(null, 0));
/******/ 		chunkLoadingGlobal.push = webpackJsonpCallback.bind(null, chunkLoadingGlobal.push.bind(chunkLoadingGlobal));
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/nonce */
/******/ 	__webpack_require__.nc = undefined;
/******/ 	
/************************************************************************/
let __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be isolated against other modules in the chunk.
(() => {
/*!**********************************************!*\
  !*** ./assets/jsx/workflow-editor/index.jsx ***!
  \**********************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _constants__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./constants */ "./assets/jsx/workflow-editor/constants.jsx");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! react */ "react");



var container = document.getElementById(_constants__WEBPACK_IMPORTED_MODULE_0__.HTML_ELEMENT_ID);
var WorkflowEditorApp = /*#__PURE__*/(0,react__WEBPACK_IMPORTED_MODULE_2__.lazy)(function () {
  return Promise.all(/*! import() */[__webpack_require__.e("vendors-node_modules_wordpress_a11y_build-module_index_js-node_modules_wordpress_compose_buil-aec548"), __webpack_require__.e("node_modules_react-querybuilder_dnd_dist_lazy_recursive_react-dnd_-assets_jsx_workflow-editor-8f4ce5")]).then(() => (__webpack_require__(/*! ./components/app */ "./assets/jsx/workflow-editor/components/app.jsx")));
});
if (container) {
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createRoot)(container).render(/*#__PURE__*/React.createElement(react__WEBPACK_IMPORTED_MODULE_2__.Suspense, {
    fallback: /*#__PURE__*/React.createElement("div", {
      className: "workflow-editor-loading"
    }, "Loading...")
  }, /*#__PURE__*/React.createElement(WorkflowEditorApp, null)));
}
})();

/******/ })()
;
//# sourceMappingURL=workflowEditor.js.map