"use strict";
(self["webpackChunkpost_expirator"] = self["webpackChunkpost_expirator"] || []).push([["assets_jsx_workflow-editor_components_flow-editor_auto-layout_elk_jsx"],{

/***/ "./assets/jsx/workflow-editor/components/flow-editor/auto-layout/elk.jsx"
/*!*******************************************************************************!*\
  !*** ./assets/jsx/workflow-editor/components/flow-editor/auto-layout/elk.jsx ***!
  \*******************************************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__),
/* harmony export */   useLayoutedElements: () => (/* binding */ useLayoutedElements)
/* harmony export */ });
/* harmony import */ var elkjs__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! elkjs */ "./node_modules/elkjs/lib/main.js");
/* harmony import */ var elkjs__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(elkjs__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _constants__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./constants */ "./assets/jsx/workflow-editor/components/flow-editor/auto-layout/constants.jsx");
/* harmony import */ var _editor_store__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../editor-store */ "./assets/jsx/workflow-editor/components/editor-store/index.jsx");
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/data */ "@wordpress/data");
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_data__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../../../utils */ "./assets/jsx/workflow-editor/utils.jsx");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _regeneratorRuntime() { "use strict"; var r = _regenerator(), e = r.m(_regeneratorRuntime), t = (Object.getPrototypeOf ? Object.getPrototypeOf(e) : e.__proto__).constructor; function n(r) { var e = "function" == typeof r && r.constructor; return !!e && (e === t || "GeneratorFunction" === (e.displayName || e.name)); } var o = { throw: 1, return: 2, break: 3, continue: 3 }; function a(r) { var e, t; return function (n) { e || (e = { stop: function stop() { return t(n.a, 2); }, catch: function _catch() { return n.v; }, abrupt: function abrupt(r, e) { return t(n.a, o[r], e); }, delegateYield: function delegateYield(r, o, a) { return e.resultName = o, t(n.d, _regeneratorValues(r), a); }, finish: function finish(r) { return t(n.f, r); } }, t = function t(r, _t, o) { n.p = e.prev, n.n = e.next; try { return r(_t, o); } finally { e.next = n.n; } }), e.resultName && (e[e.resultName] = n.v, e.resultName = void 0), e.sent = n.v, e.next = n.n; try { return r.call(this, e); } finally { n.p = e.prev, n.n = e.next; } }; } return (_regeneratorRuntime = function _regeneratorRuntime() { return { wrap: function wrap(e, t, n, o) { return r.w(a(e), t, n, o && o.reverse()); }, isGeneratorFunction: n, mark: r.m, awrap: function awrap(r, e) { return new _OverloadYield(r, e); }, AsyncIterator: _regeneratorAsyncIterator, async: function async(r, e, t, o, u) { return (n(e) ? _regeneratorAsyncGen : _regeneratorAsync)(a(r), e, t, o, u); }, keys: _regeneratorKeys, values: _regeneratorValues }; })(); }
function _regeneratorValues(e) { if (null != e) { var t = e["function" == typeof Symbol && Symbol.iterator || "@@iterator"], r = 0; if (t) return t.call(e); if ("function" == typeof e.next) return e; if (!isNaN(e.length)) return { next: function next() { return e && r >= e.length && (e = void 0), { value: e && e[r++], done: !e }; } }; } throw new TypeError(_typeof(e) + " is not iterable"); }
function _regeneratorKeys(e) { var n = Object(e), r = []; for (var t in n) r.unshift(t); return function e() { for (; r.length;) if ((t = r.pop()) in n) return e.value = t, e.done = !1, e; return e.done = !0, e; }; }
function _regeneratorAsync(n, e, r, t, o) { var a = _regeneratorAsyncGen(n, e, r, t, o); return a.next().then(function (n) { return n.done ? n.value : a.next(); }); }
function _regeneratorAsyncGen(r, e, t, o, n) { return new _regeneratorAsyncIterator(_regenerator().w(r, e, t, o), n || Promise); }
function _regeneratorAsyncIterator(t, e) { function n(r, o, i, f) { try { var c = t[r](o), u = c.value; return u instanceof _OverloadYield ? e.resolve(u.v).then(function (t) { n("next", t, i, f); }, function (t) { n("throw", t, i, f); }) : e.resolve(u).then(function (t) { c.value = t, i(c); }, function (t) { return n("throw", t, i, f); }); } catch (t) { f(t); } } var r; this.next || (_regeneratorDefine2(_regeneratorAsyncIterator.prototype), _regeneratorDefine2(_regeneratorAsyncIterator.prototype, "function" == typeof Symbol && Symbol.asyncIterator || "@asyncIterator", function () { return this; })), _regeneratorDefine2(this, "_invoke", function (t, o, i) { function f() { return new e(function (e, r) { n(t, i, e, r); }); } return r = r ? r.then(f, f) : f(); }, !0); }
function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? (c > 1 && (G.n = c < 3 ? -1 : u), d(c, u)) : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i.return) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, _regeneratorDefine2(e, r, n, t); }
function _OverloadYield(e, d) { this.v = e, this.k = d; }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, e) { if ("object" != _typeof(t) || !t) return t; var r; if ("undefined" != typeof Symbol && void 0 !== (r = t[Symbol.toPrimitive])) { var i = r.call(t, e || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === e ? String : Number)(t); }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }





var useLayoutedElements = function useLayoutedElements(_ref) {
  var nodes = _ref.nodes,
    edges = _ref.edges,
    onLayout = _ref.onLayout,
    _ref$onAnimationFrame = _ref.onAnimationFrame,
    onAnimationFrame = _ref$onAnimationFrame === void 0 ? function () {
      return null;
    } : _ref$onAnimationFrame;
  return function (_ref2) {
    var direction = _ref2.direction;
    // Elk has a *huge* amount of options to configure. To see everything you can
    // tweak check out:
    //
    // - https://www.eclipse.org/elk/reference/algorithms.html
    // - https://www.eclipse.org/elk/reference/options.html
    var opts = {
      'elk.direction': direction,
      'elk.algorithm': 'mrtree',
      'elk.spacing.nodeNode': '60'
    };
    var getLayoutedElements = /*#__PURE__*/function () {
      var _ref3 = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee(nodes, edges) {
        var options,
          isHorizontal,
          getNodeList,
          getEdgeList,
          graph,
          elk,
          layoutedGraph,
          _args = arguments;
        return _regeneratorRuntime().wrap(function _callee$(_context) {
          while (1) switch (_context.prev = _context.next) {
            case 0:
              options = _args.length > 2 && _args[2] !== undefined ? _args[2] : {};
              isHorizontal = (options === null || options === void 0 ? void 0 : options['elk.direction']) === _constants__WEBPACK_IMPORTED_MODULE_1__.AUTO_LAYOUT_DIRECTION_RIGHT;
              getNodeList = function getNodeList(nodes) {
                return nodes.map(function (node) {
                  var nodeElement = document.querySelector("div[data-id=\"".concat(node.id, "\"]"));
                  var width = (nodeElement === null || nodeElement === void 0 ? void 0 : nodeElement.offsetWidth) || 127;
                  var height = (nodeElement === null || nodeElement === void 0 ? void 0 : nodeElement.offsetHeight) || 62;
                  return _objectSpread(_objectSpread({}, node), {}, {
                    // Adjust the target and source handle positions based on the layout
                    // direction.
                    targetPosition: isHorizontal ? 'left' : 'top',
                    sourcePosition: isHorizontal ? 'right' : 'bottom',
                    // Hardcode a width and height for elk to use when layouting.
                    width: width,
                    height: height
                  });
                });
              };
              getEdgeList = function getEdgeList(edges) {
                // Sort grouping by source node
                var edgesList = edges.sort(function (edgeA, edgeB) {
                  return edgeA.source.localeCompare(edgeB.source);
                });

                // Add a order property to the edges based on the source handle name
                edgesList = edgesList.map(function (edge) {
                  var sourceHandleNames = (0,_utils__WEBPACK_IMPORTED_MODULE_4__.getNodeSourceHandleNamesByNodeId)(edge.source);
                  var orderInSourceHandleNames = sourceHandleNames.indexOf(edge.sourceHandle);
                  return _objectSpread(_objectSpread({}, edge), {}, {
                    order: orderInSourceHandleNames
                  });
                });

                // Sort edges based on the order property to avoid crossing the connections
                edgesList = edgesList.sort(function (edgeA, edgeB) {
                  return edgeA.order - edgeB.order;
                });
                return edgesList;
              };
              console.log(getEdgeList(edges));
              graph = {
                id: 'root',
                layoutOptions: options,
                children: getNodeList(nodes),
                edges: getEdgeList(edges)
              };
              elk = new (elkjs__WEBPACK_IMPORTED_MODULE_0___default())();
              _context.prev = 7;
              _context.next = 10;
              return elk.layout(graph);
            case 10:
              layoutedGraph = _context.sent;
              return _context.abrupt("return", {
                nodes: layoutedGraph.children.map(function (node_1) {
                  return _objectSpread(_objectSpread({}, node_1), {}, {
                    // React Flow expects a position property on the node instead of `x`
                    // and `y` fields.
                    position: {
                      x: node_1.x,
                      y: node_1.y
                    }
                  });
                }),
                edges: layoutedGraph.edges
              });
            case 14:
              _context.prev = 14;
              _context.t0 = _context["catch"](7);
              return _context.abrupt("return", console.error(_context.t0));
            case 17:
            case "end":
              return _context.stop();
          }
        }, _callee, null, [[7, 14]]);
      }));
      return function getLayoutedElements(_x, _x2) {
        return _ref3.apply(this, arguments);
      };
    }();
    getLayoutedElements(nodes, edges, opts).then(function (_ref4) {
      var layoutedNodes = _ref4.nodes,
        layoutedEdges = _ref4.edges;
      onLayout(layoutedNodes, layoutedEdges);
      window.requestAnimationFrame(function () {
        return onAnimationFrame();
      });
    });
  };
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (useLayoutedElements);

/***/ }

}]);
//# sourceMappingURL=assets_jsx_workflow-editor_components_flow-editor_auto-layout_elk_jsx.js.map