/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./assets/jsx/components/ButtonsPanel.jsx":
/*!************************************************!*\
  !*** ./assets/jsx/components/ButtonsPanel.jsx ***!
  \************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   ButtonsPanel: () => (/* binding */ ButtonsPanel)
/* harmony export */ });
/*
 * Copyright (c) 2025, Ramble Ventures
 */

var ButtonsPanel = function ButtonsPanel(props) {
  return /*#__PURE__*/React.createElement("div", null, props.children);
};

/***/ }),

/***/ "./assets/jsx/components/CheckboxControl.jsx":
/*!***************************************************!*\
  !*** ./assets/jsx/components/CheckboxControl.jsx ***!
  \***************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   CheckboxControl: () => (/* binding */ CheckboxControl)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../utils */ "./assets/jsx/utils.jsx");
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t.return && (u = t.return(), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }
/*
 * Copyright (c) 2025, Ramble Ventures
 */



var CheckboxControl = function CheckboxControl(props) {
  var _useState = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(props.checked || false),
    _useState2 = _slicedToArray(_useState, 2),
    checked = _useState2[0],
    setChecked = _useState2[1];
  var description;
  if (props.unescapedDescription) {
    // If using this option, the HTML has to be escaped before injected into the JS interface.
    description = /*#__PURE__*/React.createElement("p", {
      className: "description",
      dangerouslySetInnerHTML: {
        __html: (0,_utils__WEBPACK_IMPORTED_MODULE_2__.stripTags)(props.description)
      }
    });
  } else {
    description = /*#__PURE__*/React.createElement("p", {
      className: "description"
    }, props.description);
  }
  var onChange = function onChange(value) {
    setChecked(value);
    if (props.onChange) {
      props.onChange(value);
    }
  };
  return /*#__PURE__*/React.createElement(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, /*#__PURE__*/React.createElement(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.CheckboxControl, {
    label: props.label,
    name: props.name,
    id: props.name,
    className: props.className,
    checked: checked || false,
    onChange: onChange
  }), description);
};

/***/ }),

/***/ "./assets/jsx/components/DateOffsetPreview.jsx":
/*!*****************************************************!*\
  !*** ./assets/jsx/components/DateOffsetPreview.jsx ***!
  \*****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   DateOffsetPreview: () => (/* binding */ DateOffsetPreview),
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_url__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/url */ "@wordpress/url");
/* harmony import */ var _wordpress_url__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_url__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _publishpress_i18n__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @publishpress/i18n */ "@publishpress/i18n");
/* harmony import */ var _publishpress_i18n__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_publishpress_i18n__WEBPACK_IMPORTED_MODULE_2__);
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t.return && (u = t.return(), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }



var _wp = wp,
  apiFetch = _wp.apiFetch;
__webpack_require__(/*! ./css/dateOffsetPreview.css */ "./assets/jsx/components/css/dateOffsetPreview.css");
var DateOffsetPreview = function DateOffsetPreview(_ref) {
  var offset = _ref.offset,
    label = _ref.label,
    labelDatePreview = _ref.labelDatePreview,
    labelOffsetPreview = _ref.labelOffsetPreview,
    setValidationErrorCallback = _ref.setValidationErrorCallback,
    setHasPendingValidationCallback = _ref.setHasPendingValidationCallback,
    setHasValidDataCallback = _ref.setHasValidDataCallback,
    _ref$compactView = _ref.compactView,
    compactView = _ref$compactView === void 0 ? false : _ref$compactView;
  var _useState = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(''),
    _useState2 = _slicedToArray(_useState, 2),
    offsetPreview = _useState2[0],
    setOffsetPreview = _useState2[1];
  var _useState3 = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(),
    _useState4 = _slicedToArray(_useState3, 2),
    currentTime = _useState4[0],
    setCurrentTime = _useState4[1];
  var apiRequestControllerRef = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useRef)(new AbortController());
  var validateDateOffset = function validateDateOffset() {
    if (offset) {
      var controller = apiRequestControllerRef.current;
      if (controller) {
        controller.abort();
      }
      apiRequestControllerRef.current = new AbortController();
      var signal = apiRequestControllerRef.current.signal;
      setHasPendingValidationCallback(true);
      apiFetch({
        path: (0,_wordpress_url__WEBPACK_IMPORTED_MODULE_1__.addQueryArgs)("publishpress-future/v1/settings/validate-expire-offset"),
        method: 'POST',
        data: {
          offset: offset
        },
        signal: signal
      }).then(function (result) {
        setHasPendingValidationCallback(false);
        setHasValidDataCallback(result.isValid);
        setValidationErrorCallback(result.message);
        if (result.isValid) {
          setOffsetPreview(result.calculatedTime);
          setCurrentTime(result.currentTime);
        } else {
          setOffsetPreview('');
        }
      }).catch(function (error) {
        if (error.name === 'AbortError') {
          return;
        }
        setHasPendingValidationCallback(false);
        setHasValidDataCallback(false);
        setValidationErrorCallback(error.message);
        setOffsetPreview('');
      });
    }
  };
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(function () {
    validateDateOffset();
  }, [offset]);
  var compactClass = compactView ? ' compact' : '';
  return /*#__PURE__*/React.createElement(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, offset && /*#__PURE__*/React.createElement("div", {
    className: 'publishpress-future-date-preview' + compactClass
  }, /*#__PURE__*/React.createElement("h4", null, label), /*#__PURE__*/React.createElement("div", {
    className: "publishpress-future-date-preview-body"
  }, /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("span", {
    className: "publishpress-future-date-preview-label"
  }, labelDatePreview, ": "), /*#__PURE__*/React.createElement("span", {
    className: "publishpress-future-date-preview-value"
  }, currentTime)), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("span", {
    className: "publishpress-future-date-preview-label"
  }, labelOffsetPreview, ": "), /*#__PURE__*/React.createElement("span", {
    className: "publishpress-future-date-preview-value"
  }, offsetPreview)))));
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (DateOffsetPreview);

/***/ }),

/***/ "./assets/jsx/components/DateTimePicker.jsx":
/*!**************************************************!*\
  !*** ./assets/jsx/components/DateTimePicker.jsx ***!
  \**************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   DateTimePicker: () => (/* binding */ DateTimePicker)
/* harmony export */ });
/* harmony import */ var _time__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../time */ "./assets/jsx/time.jsx");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__);


var DateTimePicker = function DateTimePicker(_ref) {
  var currentDate = _ref.currentDate,
    onChange = _ref.onChange,
    is12Hour = _ref.is12Hour,
    startOfWeek = _ref.startOfWeek;
  if (typeof currentDate === 'number') {
    currentDate = (0,_time__WEBPACK_IMPORTED_MODULE_0__.normalizeUnixTimeToMilliseconds)(currentDate);
  }
  return /*#__PURE__*/React.createElement(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.DateTimePicker, {
    currentDate: currentDate,
    onChange: onChange,
    __nextRemoveHelpButton: true,
    is12Hour: is12Hour,
    startOfWeek: startOfWeek
  });
};

/***/ }),

/***/ "./assets/jsx/components/FutureActionPanel.jsx":
/*!*****************************************************!*\
  !*** ./assets/jsx/components/FutureActionPanel.jsx ***!
  \*****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   FutureActionPanel: () => (/* binding */ FutureActionPanel)
/* harmony export */ });
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../utils */ "./assets/jsx/utils.jsx");
/* harmony import */ var _ToggleCalendarDatePicker__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./ToggleCalendarDatePicker */ "./assets/jsx/components/ToggleCalendarDatePicker.jsx");
/* harmony import */ var _wordpress_plugins__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/plugins */ "@wordpress/plugins");
/* harmony import */ var _wordpress_plugins__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_plugins__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _FutureActionPanelAfterActionField__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./FutureActionPanelAfterActionField */ "./assets/jsx/components/FutureActionPanelAfterActionField.jsx");
/* harmony import */ var _FutureActionPanelTop__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./FutureActionPanelTop */ "./assets/jsx/components/FutureActionPanelTop.jsx");
function _createForOfIteratorHelper(r, e) { var t = "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (!t) { if (Array.isArray(r) || (t = _unsupportedIterableToArray(r)) || e && r && "number" == typeof r.length) { t && (r = t); var _n = 0, F = function F() {}; return { s: F, n: function n() { return _n >= r.length ? { done: !0 } : { done: !1, value: r[_n++] }; }, e: function e(r) { throw r; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var o, a = !0, u = !1; return { s: function s() { t = t.call(r); }, n: function n() { var r = t.next(); return a = r.done, r; }, e: function e(r) { u = !0, o = r; }, f: function f() { try { a || null == t.return || t.return(); } finally { if (u) throw o; } } }; }
function _toConsumableArray(r) { return _arrayWithoutHoles(r) || _iterableToArray(r) || _unsupportedIterableToArray(r) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _iterableToArray(r) { if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) return Array.from(r); }
function _arrayWithoutHoles(r) { if (Array.isArray(r)) return _arrayLikeToArray(r); }
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t.return && (u = t.return(), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }






var _wp$components = wp.components,
  PanelRow = _wp$components.PanelRow,
  CheckboxControl = _wp$components.CheckboxControl,
  SelectControl = _wp$components.SelectControl,
  FormTokenField = _wp$components.FormTokenField,
  Spinner = _wp$components.Spinner,
  BaseControl = _wp$components.BaseControl;
var _wp$element = wp.element,
  Fragment = _wp$element.Fragment,
  useEffect = _wp$element.useEffect,
  useState = _wp$element.useState;
var decodeEntities = wp.htmlEntities.decodeEntities;
var addQueryArgs = wp.url.addQueryArgs;
var _wp$data = wp.data,
  useSelect = _wp$data.useSelect,
  useDispatch = _wp$data.useDispatch;
var _wp = wp,
  apiFetch = _wp.apiFetch;
var FutureActionPanel = function FutureActionPanel(props) {
  var _useSelect = useSelect(function (select) {
      return {
        action: select(props.storeName).getAction(),
        date: select(props.storeName).getDate(),
        enabled: select(props.storeName).getEnabled(),
        terms: select(props.storeName).getTerms(),
        taxonomy: select(props.storeName).getTaxonomy(),
        taxonomyName: select(props.storeName).getTaxonomyName(),
        termsListByName: select(props.storeName).getTermsListByName(),
        termsListById: select(props.storeName).getTermsListById(),
        isFetchingTerms: select(props.storeName).getIsFetchingTerms(),
        calendarIsVisible: select(props.storeName).getCalendarIsVisible(),
        hasValidData: select(props.storeName).getHasValidData(),
        newStatus: select(props.storeName).getNewStatus()
      };
    }),
    action = _useSelect.action,
    date = _useSelect.date,
    enabled = _useSelect.enabled,
    terms = _useSelect.terms,
    taxonomy = _useSelect.taxonomy,
    taxonomyName = _useSelect.taxonomyName,
    termsListByName = _useSelect.termsListByName,
    termsListById = _useSelect.termsListById,
    isFetchingTerms = _useSelect.isFetchingTerms,
    calendarIsVisible = _useSelect.calendarIsVisible,
    hasValidData = _useSelect.hasValidData,
    newStatus = _useSelect.newStatus;
  var extraData = useSelect(function (select) {
    return select(props.storeName).getExtraData();
  }, [props.storeName]);
  useEffect(function () {
    if (props.context === 'block-editor' && props.onChangeData) {
      props.onChangeData('extraData', extraData);
    }
  }, [extraData, props.context, props.onChangeData]);
  var hiddenFields = props.hiddenFields || {};
  var _useState = useState(''),
    _useState2 = _slicedToArray(_useState, 2),
    validationError = _useState2[0],
    setValidationError = _useState2[1];
  var _useDispatch = useDispatch(props.storeName),
    setAction = _useDispatch.setAction,
    setDate = _useDispatch.setDate,
    setEnabled = _useDispatch.setEnabled,
    setTerms = _useDispatch.setTerms,
    setTaxonomy = _useDispatch.setTaxonomy,
    setTermsListByName = _useDispatch.setTermsListByName,
    setTermsListById = _useDispatch.setTermsListById,
    setTaxonomyName = _useDispatch.setTaxonomyName,
    setIsFetchingTerms = _useDispatch.setIsFetchingTerms,
    setCalendarIsVisible = _useDispatch.setCalendarIsVisible,
    setHasValidData = _useDispatch.setHasValidData,
    setNewStatus = _useDispatch.setNewStatus;
  var mapTermsListById = function mapTermsListById(terms) {
    if (_typeof(terms) !== 'object' || terms === null) {
      return {};
    }
    return terms.map(function (term) {
      return termsListById[term];
    });
  };
  var insertTerm = function insertTerm(term) {
    termsListByName[term] = {
      id: term,
      count: 0,
      description: "",
      link: "",
      name: term,
      slug: term,
      taxonomy: taxonomy
    };
    termsListById[term] = term;
    setTermsListByName(termsListByName);
    setTermsListById(termsListById);
    setTerms([].concat(_toConsumableArray(terms), [term]));
  };
  var mapTermsListByName = function mapTermsListByName(terms) {
    if (_typeof(terms) !== 'object' || terms === null) {
      return {};
    }
    return terms.map(function (term) {
      if (termsListByName[term]) {
        return termsListByName[term].id;
      }
      insertTerm(term);
      return term;
    });
  };
  var callOnChangeData = function callOnChangeData(attribute, value) {
    if (typeof props.onChangeData === 'function') {
      props.onChangeData(attribute, value);
    }
  };
  var handleEnabledChange = function handleEnabledChange(isChecked) {
    setEnabled(isChecked);
    if (isChecked) {
      setAction(props.action);
      setDate(props.date);
      setNewStatus(props.newStatus);
      setTerms(props.terms);
      setTaxonomy(props.taxonomy);
      fetchTerms();
    }
    callOnChangeData('enabled', isChecked);
  };
  var handleActionChange = function handleActionChange(value) {
    setAction(value);
    callOnChangeData('action', value);
  };
  var handleNewStatusChange = function handleNewStatusChange(value) {
    setNewStatus(value);
    callOnChangeData('newStatus', value);
  };
  var handleDateChange = function handleDateChange(value) {
    setDate(value);
    callOnChangeData('date', value);
  };
  var handleTermsChange = function handleTermsChange(value) {
    value = mapTermsListByName(value);
    setTerms(value);
    callOnChangeData('terms', value);
  };
  var fetchTerms = function fetchTerms() {
    var termsListByName = {};
    var termsListById = {};
    if (!taxonomy) {
      return;
    }
    setIsFetchingTerms(true);
    apiFetch({
      path: addQueryArgs("publishpress-future/v1/terms/".concat(taxonomy))
    }).then(function (result) {
      if (result.terms && Array.isArray(result.terms)) {
        result.terms.forEach(function (term) {
          termsListByName[decodeEntities(term.name)] = term;
          termsListById[term.id] = decodeEntities(term.name);
        });
      }
      setTermsListByName(termsListByName);
      setTermsListById(termsListById);
      setTaxonomyName(decodeEntities(result.taxonomyName));
      setIsFetchingTerms(false);
    });
  };
  var storeCalendarIsVisibleOnStorage = function storeCalendarIsVisibleOnStorage(value) {
    localStorage.setItem('FUTURE_ACTION_CALENDAR_IS_VISIBLE_' + props.context, value ? '1' : '0');
  };
  var getCalendarIsVisibleFromStorage = function getCalendarIsVisibleFromStorage() {
    return localStorage.getItem('FUTURE_ACTION_CALENDAR_IS_VISIBLE_' + props.context);
  };
  useEffect(function () {
    if (props.autoEnableAndHideCheckbox) {
      setEnabled(true);
    } else {
      setEnabled(props.enabled);
    }
    setAction(props.action);
    setNewStatus(props.newStatus);
    setDate(props.date);
    setTerms(props.terms);
    setTaxonomy(props.taxonomy);
    if (getCalendarIsVisibleFromStorage() === null) {
      setCalendarIsVisible(props.calendarIsVisible);
    } else {
      setCalendarIsVisible(getCalendarIsVisibleFromStorage() === '1' && !props.hideCalendarByDefault);
    }

    // We need to get the value directly from the props because the value from the store is not updated yet
    if (props.enabled) {
      if (props.isCleanNewPost) {
        // Force populate the default values
        handleEnabledChange(true);
      }
      fetchTerms();
    }
  }, []);
  useEffect(function () {
    storeCalendarIsVisibleOnStorage(calendarIsVisible);
  }, [calendarIsVisible]);
  useEffect(function () {
    if (hasValidData && props.onDataIsValid) {
      props.onDataIsValid();
    }
    if (!hasValidData && props.onDataIsInvalid) {
      props.onDataIsInvalid();
    }
  }, [hasValidData]);
  var selectedTerms = [];
  if (terms && terms.length > 0 && termsListById) {
    selectedTerms = (0,_utils__WEBPACK_IMPORTED_MODULE_0__.compact)(mapTermsListById(terms));
    if (typeof selectedTerms === 'string') {
      selectedTerms = [];
    }
  }
  var termsListByNameKeys = [];
  if (_typeof(termsListByName) === 'object' && termsListByName !== null) {
    termsListByNameKeys = Object.keys(termsListByName);
  }
  var panelClass = calendarIsVisible ? 'future-action-panel' : 'future-action-panel hidden-calendar';
  var contentPanelClass = calendarIsVisible ? 'future-action-panel-content' : 'future-action-panel-content hidden-calendar';
  var datePanelClass = calendarIsVisible ? 'future-action-date-panel' : 'future-action-date-panel hidden-calendar';
  var is24hour;
  if (props.timeFormat === 'inherited') {
    is24hour = !props.is12Hour;
  } else {
    is24hour = props.timeFormat === '24h';
  }
  var replaceCurlyBracketsWithLink = function replaceCurlyBracketsWithLink(string, href, target) {
    var parts = string.split('{');
    var result = [];
    result.push(parts.shift());
    var _iterator = _createForOfIteratorHelper(parts),
      _step;
    try {
      for (_iterator.s(); !(_step = _iterator.n()).done;) {
        var part = _step.value;
        var _part$split = part.split('}'),
          _part$split2 = _slicedToArray(_part$split, 2),
          before = _part$split2[0],
          after = _part$split2[1];
        result.push( /*#__PURE__*/React.createElement("a", {
          href: href,
          target: target,
          key: href
        }, before));
        result.push(after);
      }
    } catch (err) {
      _iterator.e(err);
    } finally {
      _iterator.f();
    }
    return result;
  };

  // Remove items from actions list if related to taxonomies and there is no taxonmoy for the post type
  var actionsSelectOptions = props.actionsSelectOptions;
  if (!props.taxonomy) {
    actionsSelectOptions = props.actionsSelectOptions.filter(function (item) {
      return ['category', 'category-add', 'category-remove', 'category-remove-all'].indexOf(item.value) === -1;
    });
  }
  var HelpText = replaceCurlyBracketsWithLink(props.strings.timezoneSettingsHelp, '/wp-admin/options-general.php#timezone_string', '_blank');
  var displayTaxonomyField = String(action).includes('category') && action !== 'category-remove-all';
  var termsFieldLabel = taxonomyName;
  switch (action) {
    case 'category':
      termsFieldLabel = props.strings.newTerms.replace('%s', taxonomyName);
      break;
    case 'category-remove':
      termsFieldLabel = props.strings.removeTerms.replace('%s', taxonomyName);
      break;
    case 'category-add':
      termsFieldLabel = props.strings.addTerms.replace('%s', taxonomyName);
      break;
  }
  var validateData = function validateData() {
    var valid = true;
    if (!enabled) {
      setValidationError('');
      return true;
    }
    if (!action) {
      setValidationError(props.strings.errorActionRequired);
      valid = false;
    }
    if (!date) {
      setValidationError(props.strings.errorDateRequired);
      valid = false;
    }
    if (date) {
      var wpTimezone = props.wpTimezone || "UTC";
      var selectedDate = new Date(date);
      var now = new Date();
      var nowInWpTimezone;
      if (wpTimezone.match(/^[+-]\d{2}:\d{2}$/)) {
        var _wpTimezone$match = wpTimezone.match(/^([+-])(\d{2}):(\d{2})$/),
          _wpTimezone$match2 = _slicedToArray(_wpTimezone$match, 4),
          sign = _wpTimezone$match2[1],
          hours = _wpTimezone$match2[2],
          minutes = _wpTimezone$match2[3];
        var offsetMinutes = (parseInt(hours) * 60 + parseInt(minutes)) * (sign === '+' ? 1 : -1);

        // Convert browser time to WordPress timezone
        // Browser offset from UTC
        var browserOffsetMinutes = now.getTimezoneOffset();

        // Adjust from browser timezone to WordPress timezone
        var totalOffsetMinutes = offsetMinutes + browserOffsetMinutes;
        nowInWpTimezone = new Date(now.getTime() + totalOffsetMinutes * 60000);
      } else {
        try {
          nowInWpTimezone = new Date(now.toLocaleString("en-US", {
            timeZone: wpTimezone
          }));
        } catch (error) {
          nowInWpTimezone = new Date(now.toLocaleString("en-US", {
            timeZone: "UTC"
          }));
        }
      }
      if (selectedDate < nowInWpTimezone) {
        setValidationError(props.strings.errorDateInPast);
        valid = false;
      }
    }
    var isTermRequired = ['category', 'category-add', 'category-remove'].includes(action);
    var noTermIsSelected = terms.length === 0 || terms.length === 1 && (terms[0] === '' || terms[0] === '0');
    if (isTermRequired && noTermIsSelected) {
      setValidationError(props.strings.errorTermsRequired);
      valid = false;
    }
    if (valid) {
      setValidationError('');
    }
    return valid;
  };
  useEffect(function () {
    if (!enabled) {
      setHasValidData(true);
      setValidationError('');
      return;
    }
    setHasValidData(validateData());
  }, [action, date, enabled, terms, taxonomy]);

  // This adds a 'cancel' class to the input when the user clicks on the
  // field to prevent the form from being submitted. This is a workaround
  // for the issue on the quick-edit form where the form is submitted when
  // the user presses the 'Enter' key trying to add a term to the field.
  var forceIgnoreAutoSubmitOnEnter = function forceIgnoreAutoSubmitOnEnter(e) {
    jQuery(e.target).addClass('cancel');
  };
  return /*#__PURE__*/React.createElement(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.SlotFillProvider, null, /*#__PURE__*/React.createElement("div", {
    className: panelClass
  }, props.autoEnableAndHideCheckbox && /*#__PURE__*/React.createElement("input", {
    type: "hidden",
    name: 'future_action_enabled',
    value: 1
  }), props.showTitle && /*#__PURE__*/React.createElement("div", {
    style: {
      fontWeight: 'bold',
      marginBottom: '10px'
    }
  }, props.strings.panelTitle), /*#__PURE__*/React.createElement(_FutureActionPanelTop__WEBPACK_IMPORTED_MODULE_5__.FutureActionPanelTop.Slot, {
    fillProps: {
      storeName: props.storeName
    }
  }), !props.autoEnableAndHideCheckbox && /*#__PURE__*/React.createElement(PanelRow, null, /*#__PURE__*/React.createElement(CheckboxControl, {
    label: props.strings.enablePostExpiration,
    checked: enabled || false,
    onChange: handleEnabledChange,
    className: "future-action-enable-checkbox"
  })), enabled && /*#__PURE__*/React.createElement(Fragment, null, !hiddenFields['_expiration-date-type'] && /*#__PURE__*/React.createElement(PanelRow, {
    className: contentPanelClass + ' future-action-full-width'
  }, /*#__PURE__*/React.createElement(SelectControl, {
    label: props.strings.action,
    value: action,
    options: actionsSelectOptions,
    onChange: handleActionChange,
    className: "future-action-select-action"
  })), /*#__PURE__*/React.createElement(_FutureActionPanelAfterActionField__WEBPACK_IMPORTED_MODULE_4__.FutureActionPanelAfterActionField.Slot, {
    fillProps: {
      storeName: props.storeName
    }
  }), !hiddenFields['_expiration-date-post-status'] && action === 'change-status' && /*#__PURE__*/React.createElement(PanelRow, {
    className: "new-status"
  }, /*#__PURE__*/React.createElement(SelectControl, {
    label: props.strings.newStatus,
    options: props.statusesSelectOptions,
    value: newStatus,
    onChange: handleNewStatusChange,
    className: "future-action-select-new-status"
  })), !hiddenFields['_expiration-date-taxonomy'] && displayTaxonomyField && (isFetchingTerms && /*#__PURE__*/React.createElement(PanelRow, null, /*#__PURE__*/React.createElement(BaseControl, {
    label: taxonomyName
  }, "".concat(props.strings.loading, " (").concat(taxonomyName, ")"), /*#__PURE__*/React.createElement(Spinner, null))) || !taxonomy && /*#__PURE__*/React.createElement(PanelRow, null, /*#__PURE__*/React.createElement(BaseControl, {
    label: taxonomyName,
    className: "future-action-warning"
  }, /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("i", {
    className: "dashicons dashicons-warning"
  }), " ", props.strings.noTaxonomyFound))) || termsListByNameKeys.length === 0 && /*#__PURE__*/React.createElement(PanelRow, null, /*#__PURE__*/React.createElement(BaseControl, {
    label: taxonomyName,
    className: "future-action-warning"
  }, /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("i", {
    className: "dashicons dashicons-warning"
  }), " ", props.strings.noTermsFound))) || /*#__PURE__*/React.createElement(PanelRow, {
    className: "future-action-full-width"
  }, /*#__PURE__*/React.createElement(BaseControl, null, /*#__PURE__*/React.createElement(FormTokenField, {
    label: termsFieldLabel,
    value: selectedTerms,
    suggestions: termsListByNameKeys,
    onChange: handleTermsChange,
    placeholder: props.strings.addTermsPlaceholder,
    className: "future-action-terms",
    maxSuggestions: 1000,
    onFocus: forceIgnoreAutoSubmitOnEnter,
    __experimentalExpandOnFocus: true,
    __experimentalAutoSelectFirstMatch: true
  })))), !hiddenFields['_expiration-date'] && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement(PanelRow, {
    className: datePanelClass
  }, /*#__PURE__*/React.createElement(_ToggleCalendarDatePicker__WEBPACK_IMPORTED_MODULE_1__.ToggleCalendarDatePicker, {
    currentDate: date,
    onChangeDate: handleDateChange,
    onToggleCalendar: function onToggleCalendar() {
      return setCalendarIsVisible(!calendarIsVisible);
    },
    is12Hour: !is24hour,
    startOfWeek: props.startOfWeek,
    isExpanded: calendarIsVisible,
    strings: props.strings
  })), /*#__PURE__*/React.createElement(PanelRow, null, /*#__PURE__*/React.createElement("div", {
    className: "future-action-help-text"
  }, /*#__PURE__*/React.createElement("hr", null), /*#__PURE__*/React.createElement("span", {
    className: "dashicons dashicons-info"
  }), " ", HelpText))), !hasValidData && /*#__PURE__*/React.createElement(PanelRow, null, /*#__PURE__*/React.createElement(BaseControl, {
    className: "notice notice-error"
  }, /*#__PURE__*/React.createElement("div", null, validationError))))), /*#__PURE__*/React.createElement(_wordpress_plugins__WEBPACK_IMPORTED_MODULE_2__.PluginArea, {
    scope: "publishpress-future"
  }));
};

/***/ }),

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

/***/ "./assets/jsx/components/FutureActionPanelBlockEditor.jsx":
/*!****************************************************************!*\
  !*** ./assets/jsx/components/FutureActionPanelBlockEditor.jsx ***!
  \****************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   FutureActionPanelBlockEditor: () => (/* binding */ FutureActionPanelBlockEditor)
/* harmony export */ });
/* harmony import */ var ___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./ */ "./assets/jsx/components/index.jsx");
/* harmony import */ var _css_block_editor_css__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./css/block-editor.css */ "./assets/jsx/components/css/block-editor.css");
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t.return && (u = t.return(), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }


var FutureActionPanelBlockEditor = function FutureActionPanelBlockEditor(props) {
  var PluginDocumentSettingPanel = wp.editPost.PluginDocumentSettingPanel;
  var _wp$data = wp.data,
    useDispatch = _wp$data.useDispatch,
    select = _wp$data.select;
  var _useDispatch = useDispatch('core/editor'),
    editPost = _useDispatch.editPost;
  var editPostAttribute = function editPostAttribute(newAttribute) {
    var attribute = {
      publishpress_future_action: {}
    };

    // For each property on newAttribute, set the value on attribute
    for (var _i = 0, _Object$entries = Object.entries(newAttribute); _i < _Object$entries.length; _i++) {
      var _Object$entries$_i = _slicedToArray(_Object$entries[_i], 2),
        name = _Object$entries$_i[0],
        value = _Object$entries$_i[1];
      attribute.publishpress_future_action[name] = value;
    }
    editPost(attribute);
  };
  var onChangeData = function onChangeData(attribute, value) {
    var store = select(props.storeName);
    var newAttribute = {
      'enabled': store.getEnabled()
    };
    newAttribute['action'] = store.getAction();
    newAttribute['newStatus'] = store.getNewStatus();
    newAttribute['date'] = store.getDate();
    newAttribute['terms'] = store.getTerms();
    newAttribute['taxonomy'] = store.getTaxonomy();
    newAttribute['extraData'] = store.getExtraData();
    editPostAttribute(newAttribute);
  };
  var rawData = select('core/editor').getEditedPostAttribute('publishpress_future_action');
  var data = rawData || {
    enabled: false,
    action: '',
    newStatus: '',
    date: '',
    terms: [],
    taxonomy: '',
    extraData: {}
  };
  var _useDispatch2 = useDispatch('core/editor'),
    lockPostSaving = _useDispatch2.lockPostSaving,
    unlockPostSaving = _useDispatch2.unlockPostSaving;
  var onDataIsValid = function onDataIsValid() {
    unlockPostSaving('future-action');
  };
  var onDataIsInvalid = function onDataIsInvalid() {
    lockPostSaving('future-action');
  };
  return /*#__PURE__*/React.createElement(PluginDocumentSettingPanel, {
    name: 'publishpress-future-action-panel',
    title: props.strings.panelTitle,
    initialOpen: props.postTypeDefaultConfig.autoEnable,
    className: 'post-expirator-panel'
  }, /*#__PURE__*/React.createElement("div", {
    id: "publishpress-future-block-editor"
  }, /*#__PURE__*/React.createElement(___WEBPACK_IMPORTED_MODULE_0__.FutureActionPanel, {
    context: 'block-editor',
    postType: props.postType,
    isCleanNewPost: props.isCleanNewPost,
    actionsSelectOptions: props.actionsSelectOptions,
    statusesSelectOptions: props.statusesSelectOptions,
    enabled: data.enabled,
    calendarIsVisible: true,
    action: data.action,
    newStatus: data.newStatus,
    date: data.date,
    terms: data.terms,
    taxonomy: data.taxonomy,
    taxonomyName: props.taxonomyName,
    onChangeData: onChangeData,
    is12Hour: props.is12Hour,
    timeFormat: props.timeFormat,
    startOfWeek: props.startOfWeek,
    storeName: props.storeName,
    strings: props.strings,
    onDataIsValid: onDataIsValid,
    hideCalendarByDefault: props.hideCalendarByDefault,
    hiddenFields: props.hiddenFields,
    wpTimezone: props.wpTimezone,
    showTitle: false,
    onDataIsInvalid: onDataIsInvalid
  })));
};

/***/ }),

/***/ "./assets/jsx/components/FutureActionPanelBulkEdit.jsx":
/*!*************************************************************!*\
  !*** ./assets/jsx/components/FutureActionPanelBulkEdit.jsx ***!
  \*************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   FutureActionPanelBulkEdit: () => (/* binding */ FutureActionPanelBulkEdit)
/* harmony export */ });
/* harmony import */ var ___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! . */ "./assets/jsx/components/index.jsx");
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../utils */ "./assets/jsx/utils.jsx");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }


var FutureActionPanelBulkEdit = function FutureActionPanelBulkEdit(props) {
  var _wp$data = wp.data,
    useSelect = _wp$data.useSelect,
    useDispatch = _wp$data.useDispatch,
    select = _wp$data.select;
  var useEffect = wp.element.useEffect;
  var onChangeData = function onChangeData(attribute, value) {
    (0,_utils__WEBPACK_IMPORTED_MODULE_1__.getElementByName)('future_action_bulk_enabled').value = select(props.storeName).getEnabled() ? 1 : 0;
    (0,_utils__WEBPACK_IMPORTED_MODULE_1__.getElementByName)('future_action_bulk_action').value = select(props.storeName).getAction();
    (0,_utils__WEBPACK_IMPORTED_MODULE_1__.getElementByName)('future_action_bulk_new_status').value = select(props.storeName).getNewStatus();
    (0,_utils__WEBPACK_IMPORTED_MODULE_1__.getElementByName)('future_action_bulk_date').value = select(props.storeName).getDate();
    (0,_utils__WEBPACK_IMPORTED_MODULE_1__.getElementByName)('future_action_bulk_terms').value = select(props.storeName).getTerms().join(',');
    (0,_utils__WEBPACK_IMPORTED_MODULE_1__.getElementByName)('future_action_bulk_taxonomy').value = select(props.storeName).getTaxonomy();
  };
  var date = useSelect(function (select) {
    return select(props.storeName).getDate();
  }, []);
  var enabled = useSelect(function (select) {
    return select(props.storeName).getEnabled();
  }, []);
  var action = useSelect(function (select) {
    return select(props.storeName).getAction();
  }, []);
  var newStatus = useSelect(function (select) {
    return select(props.storeName).getNewStatus();
  }, []);
  var terms = useSelect(function (select) {
    return select(props.storeName).getTerms();
  }, []);
  var taxonomy = useSelect(function (select) {
    return select(props.storeName).getTaxonomy();
  }, []);
  var changeAction = useSelect(function (select) {
    return select(props.storeName).getChangeAction();
  }, []);
  var hasValidData = useSelect(function (select) {
    return select(props.storeName).getHasValidData();
  }, []);
  var _useDispatch = useDispatch(props.storeName),
    setChangeAction = _useDispatch.setChangeAction;
  var termsString = terms;
  if (_typeof(terms) === 'object') {
    termsString = terms.join(',');
  }
  var handleStrategyChange = function handleStrategyChange(value) {
    setChangeAction(value);
  };
  var options = [{
    value: 'no-change',
    label: props.strings.noChange
  }, {
    value: 'change-add',
    label: props.strings.changeAdd
  }, {
    value: 'add-only',
    label: props.strings.addOnly
  }, {
    value: 'change-only',
    label: props.strings.changeOnly
  }, {
    value: 'remove-only',
    label: props.strings.removeOnly
  }];
  var optionsToDisplayPanel = ['change-add', 'add-only', 'change-only'];
  useEffect(function () {
    // We are not using onDataIsValid and onDataIsInvalid because we need to enable/disable the button
    // also based on the changeAction value.
    if (hasValidData || changeAction === 'no-change') {
      jQuery('#bulk_edit').prop('disabled', false);
    } else {
      jQuery('#bulk_edit').prop('disabled', true);
    }
  }, [hasValidData, changeAction]);
  return /*#__PURE__*/React.createElement("div", {
    className: 'post-expirator-panel'
  }, /*#__PURE__*/React.createElement(___WEBPACK_IMPORTED_MODULE_0__.SelectControl, {
    label: props.strings.futureActionUpdate,
    name: 'future_action_bulk_change_action',
    value: changeAction,
    options: options,
    onChange: handleStrategyChange
  }), optionsToDisplayPanel.includes(changeAction) && /*#__PURE__*/React.createElement(___WEBPACK_IMPORTED_MODULE_0__.FutureActionPanel, {
    context: 'bulk-edit',
    autoEnableAndHideCheckbox: true,
    postType: props.postType,
    isCleanNewPost: props.isNewPost,
    actionsSelectOptions: props.actionsSelectOptions,
    statusesSelectOptions: props.statusesSelectOptions,
    enabled: true,
    calendarIsVisible: false,
    action: action,
    newStatus: newStatus,
    date: date,
    terms: terms,
    taxonomy: taxonomy,
    taxonomyName: props.taxonomyName,
    onChangeData: onChangeData,
    is12Hour: props.is12Hour,
    timeFormat: props.timeFormat,
    startOfWeek: props.startOfWeek,
    storeName: props.storeName,
    hideCalendarByDefault: props.hideCalendarByDefault,
    hiddenFields: props.hiddenFields,
    wpTimezone: props.wpTimezone,
    showTitle: false,
    strings: props.strings
  }), /*#__PURE__*/React.createElement("input", {
    type: "hidden",
    name: 'future_action_bulk_enabled',
    value: enabled ? 1 : 0
  }), /*#__PURE__*/React.createElement("input", {
    type: "hidden",
    name: 'future_action_bulk_action',
    value: action
  }), /*#__PURE__*/React.createElement("input", {
    type: "hidden",
    name: 'future_action_bulk_new_status',
    value: newStatus
  }), /*#__PURE__*/React.createElement("input", {
    type: "hidden",
    name: 'future_action_bulk_date',
    value: date
  }), /*#__PURE__*/React.createElement("input", {
    type: "hidden",
    name: 'future_action_bulk_terms',
    value: termsString
  }), /*#__PURE__*/React.createElement("input", {
    type: "hidden",
    name: 'future_action_bulk_taxonomy',
    value: taxonomy
  }), /*#__PURE__*/React.createElement("input", {
    type: "hidden",
    name: 'future_action_bulk_view',
    value: "bulk-edit"
  }), /*#__PURE__*/React.createElement("input", {
    type: "hidden",
    name: '_future_action_nonce',
    value: props.nonce
  }));
};

/***/ }),

/***/ "./assets/jsx/components/FutureActionPanelClassicEditor.jsx":
/*!******************************************************************!*\
  !*** ./assets/jsx/components/FutureActionPanelClassicEditor.jsx ***!
  \******************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   FutureActionPanelClassicEditor: () => (/* binding */ FutureActionPanelClassicEditor)
/* harmony export */ });
/* harmony import */ var ___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./ */ "./assets/jsx/components/index.jsx");
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/data */ "@wordpress/data");
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_data__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_2__);



var FutureActionPanelClassicEditor = function FutureActionPanelClassicEditor(props) {
  var browserTimezoneOffset = new Date().getTimezoneOffset();
  var getElementByName = function getElementByName(name) {
    return document.getElementsByName(name)[0];
  };
  var onChangeData = function onChangeData(attribute, value) {
    var store = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_1__.select)(props.storeName);
    getElementByName('future_action_enabled').value = store.getEnabled() ? 1 : 0;
    getElementByName('future_action_action').value = store.getAction();
    getElementByName('future_action_new_status').value = store.getNewStatus();
    getElementByName('future_action_date').value = store.getDate();
    getElementByName('future_action_terms').value = store.getTerms().join(',');
    getElementByName('future_action_taxonomy').value = store.getTaxonomy();
  };
  var getTermsFromElementByName = function getTermsFromElementByName(name) {
    var element = getElementByName(name);
    if (!element) {
      return [];
    }
    var terms = element.value.split(',');
    if (terms.length === 1 && terms[0] === '') {
      terms = [];
    }
    return terms.map(function (term) {
      return parseInt(term);
    });
  };
  var getElementValueByName = function getElementValueByName(name) {
    var element = getElementByName(name);
    if (!element) {
      return '';
    }
    return element.value;
  };
  var data = {
    enabled: getElementValueByName('future_action_enabled') === '1',
    action: getElementValueByName('future_action_action'),
    newStatus: getElementValueByName('future_action_new_status'),
    date: getElementValueByName('future_action_date'),
    terms: getTermsFromElementByName('future_action_terms'),
    taxonomy: getElementValueByName('future_action_taxonomy')
  };
  var onDataIsValid = function onDataIsValid() {
    jQuery('#publish').prop('disabled', false);
  };
  var onDataIsInvalid = function onDataIsInvalid() {
    jQuery('#publish').prop('disabled', true);
  };
  return /*#__PURE__*/React.createElement("div", {
    className: 'post-expirator-panel'
  }, /*#__PURE__*/React.createElement(___WEBPACK_IMPORTED_MODULE_0__.FutureActionPanel, {
    context: 'classic-editor',
    postType: props.postType,
    isCleanNewPost: props.isNewPost,
    actionsSelectOptions: props.actionsSelectOptions,
    statusesSelectOptions: props.statusesSelectOptions,
    enabled: data.enabled,
    calendarIsVisible: true,
    action: data.action,
    newStatus: data.newStatus,
    date: data.date,
    terms: data.terms,
    taxonomy: data.taxonomy,
    taxonomyName: props.taxonomyName,
    onChangeData: onChangeData,
    is12Hour: props.is12Hour,
    timeFormat: props.timeFormat,
    startOfWeek: props.startOfWeek,
    storeName: props.storeName,
    strings: props.strings,
    onDataIsValid: onDataIsValid,
    hideCalendarByDefault: props.hideCalendarByDefault,
    hiddenFields: props.hiddenFields,
    wpTimezone: props.wpTimezone,
    showTitle: false,
    onDataIsInvalid: onDataIsInvalid
  }));
};

/***/ }),

/***/ "./assets/jsx/components/FutureActionPanelQuickEdit.jsx":
/*!**************************************************************!*\
  !*** ./assets/jsx/components/FutureActionPanelQuickEdit.jsx ***!
  \**************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   FutureActionPanelQuickEdit: () => (/* binding */ FutureActionPanelQuickEdit)
/* harmony export */ });
/* harmony import */ var ___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./ */ "./assets/jsx/components/index.jsx");
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/data */ "@wordpress/data");
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_data__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_2__);
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }



var FutureActionPanelQuickEdit = function FutureActionPanelQuickEdit(props) {
  var onChangeData = function onChangeData(attribute, value) {};
  var date = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_1__.useSelect)(function (select) {
    return select(props.storeName).getDate();
  }, []);
  var enabled = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_1__.useSelect)(function (select) {
    return select(props.storeName).getEnabled();
  }, []);
  var action = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_1__.useSelect)(function (select) {
    return select(props.storeName).getAction();
  }, []);
  var terms = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_1__.useSelect)(function (select) {
    return select(props.storeName).getTerms();
  }, []);
  var taxonomy = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_1__.useSelect)(function (select) {
    return select(props.storeName).getTaxonomy();
  }, []);
  var hasValidData = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_1__.useSelect)(function (select) {
    return select(props.storeName).getHasValidData();
  }, []);
  var newStatus = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_1__.useSelect)(function (select) {
    return select(props.storeName).getNewStatus();
  }, []);
  var termsString = terms;
  if (_typeof(terms) === 'object') {
    termsString = terms.join(',');
  }
  var onDataIsValid = function onDataIsValid() {
    jQuery('.button-primary.save').prop('disabled', false);
  };
  var onDataIsInvalid = function onDataIsInvalid() {
    jQuery('.button-primary.save').prop('disabled', true);
  };
  return /*#__PURE__*/React.createElement("div", {
    className: 'post-expirator-panel'
  }, /*#__PURE__*/React.createElement(___WEBPACK_IMPORTED_MODULE_0__.FutureActionPanel, {
    context: 'quick-edit',
    postType: props.postType,
    isCleanNewPost: props.isNewPost,
    actionsSelectOptions: props.actionsSelectOptions,
    statusesSelectOptions: props.statusesSelectOptions,
    enabled: enabled,
    calendarIsVisible: false,
    action: action,
    newStatus: newStatus,
    date: date,
    terms: terms,
    taxonomy: taxonomy,
    taxonomyName: props.taxonomyName,
    onChangeData: onChangeData,
    is12Hour: props.is12Hour,
    timeFormat: props.timeFormat,
    startOfWeek: props.startOfWeek,
    storeName: props.storeName,
    strings: props.strings,
    onDataIsValid: onDataIsValid,
    hideCalendarByDefault: props.hideCalendarByDefault,
    hiddenFields: props.hiddenFields,
    wpTimezone: props.wpTimezone,
    showTitle: true,
    onDataIsInvalid: onDataIsInvalid
  }), /*#__PURE__*/React.createElement("input", {
    type: "hidden",
    name: 'future_action_enabled',
    value: enabled ? 1 : 0
  }), /*#__PURE__*/React.createElement("input", {
    type: "hidden",
    name: 'future_action_action',
    value: action ? action : ''
  }), /*#__PURE__*/React.createElement("input", {
    type: "hidden",
    name: 'future_action_new_status',
    value: newStatus ? newStatus : ''
  }), /*#__PURE__*/React.createElement("input", {
    type: "hidden",
    name: 'future_action_date',
    value: date ? date : ''
  }), /*#__PURE__*/React.createElement("input", {
    type: "hidden",
    name: 'future_action_terms',
    value: termsString ? termsString : ''
  }), /*#__PURE__*/React.createElement("input", {
    type: "hidden",
    name: 'future_action_taxonomy',
    value: taxonomy ? taxonomy : ''
  }), /*#__PURE__*/React.createElement("input", {
    type: "hidden",
    name: 'future_action_view',
    value: "quick-edit"
  }), /*#__PURE__*/React.createElement("input", {
    type: "hidden",
    name: '_future_action_nonce',
    value: props.nonce
  }));
};

/***/ }),

/***/ "./assets/jsx/components/FutureActionPanelTop.jsx":
/*!********************************************************!*\
  !*** ./assets/jsx/components/FutureActionPanelTop.jsx ***!
  \********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   FutureActionPanelTop: () => (/* binding */ FutureActionPanelTop),
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_0__);
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }

var FutureActionPanelTop = function FutureActionPanelTop(_ref) {
  var children = _ref.children;
  return /*#__PURE__*/React.createElement(_wordpress_components__WEBPACK_IMPORTED_MODULE_0__.Fill, {
    name: "FutureActionPanelTop"
  }, children);
};
var FutureActionPanelTopSlot = function FutureActionPanelTopSlot(props) {
  return /*#__PURE__*/React.createElement(_wordpress_components__WEBPACK_IMPORTED_MODULE_0__.Slot, _extends({
    name: "FutureActionPanelTop"
  }, props));
};
FutureActionPanelTop.Slot = FutureActionPanelTopSlot;
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (FutureActionPanelTop);

/***/ }),

/***/ "./assets/jsx/components/NonceControl.jsx":
/*!************************************************!*\
  !*** ./assets/jsx/components/NonceControl.jsx ***!
  \************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   NonceControl: () => (/* binding */ NonceControl)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/*
 * Copyright (c) 2025, Ramble Ventures
 */

var NonceControl = function NonceControl(props) {
  if (!props.name) {
    props.name = '_wpnonce';
  }
  if (!props.referrer) {
    props.referrer = true;
  }
  return /*#__PURE__*/React.createElement(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, /*#__PURE__*/React.createElement("input", {
    type: "hidden",
    name: props.name,
    id: props.name,
    value: props.nonce
  }), props.referrer && /*#__PURE__*/React.createElement("input", {
    type: "hidden",
    name: "_wp_http_referer",
    value: props.referrer
  }));
};

/***/ }),

/***/ "./assets/jsx/components/PostTypeSettingsPanel.jsx":
/*!*********************************************************!*\
  !*** ./assets/jsx/components/PostTypeSettingsPanel.jsx ***!
  \*********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   PostTypeSettingsPanel: () => (/* binding */ PostTypeSettingsPanel)
/* harmony export */ });
/* harmony import */ var ___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./ */ "./assets/jsx/components/index.jsx");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_url__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/url */ "@wordpress/url");
/* harmony import */ var _wordpress_url__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_url__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_hooks__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/hooks */ "@wordpress/hooks");
/* harmony import */ var _wordpress_hooks__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_hooks__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _DateOffsetPreview__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./DateOffsetPreview */ "./assets/jsx/components/DateOffsetPreview.jsx");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_5__);
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t.return && (u = t.return(), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }
/*
 * Copyright (c) 2025, Ramble Ventures
 */







var _wp = wp,
  apiFetch = _wp.apiFetch;
var PanelRow = wp.components.PanelRow;
var PostTypeSettingsPanel = function PostTypeSettingsPanel(props) {
  var originalExpireTypeList = props.expireTypeList[props.postType];
  var _useState = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)(props.settings.taxonomy),
    _useState2 = _slicedToArray(_useState, 2),
    postTypeTaxonomy = _useState2[0],
    setPostTypeTaxonomy = _useState2[1];
  var _useState3 = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)([]),
    _useState4 = _slicedToArray(_useState3, 2),
    termOptions = _useState4[0],
    setTermOptions = _useState4[1];
  var _useState5 = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)(false),
    _useState6 = _slicedToArray(_useState5, 2),
    termsSelectIsLoading = _useState6[0],
    setTermsSelectIsLoading = _useState6[1];
  var _useState7 = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)([]),
    _useState8 = _slicedToArray(_useState7, 2),
    selectedTerms = _useState8[0],
    setSelectedTerms = _useState8[1];
  var _useState9 = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)(props.settings.howToExpire),
    _useState10 = _slicedToArray(_useState9, 2),
    settingHowToExpire = _useState10[0],
    setSettingHowToExpire = _useState10[1];
  var _useState11 = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)(props.settings.active),
    _useState12 = _slicedToArray(_useState11, 2),
    isActive = _useState12[0],
    setIsActive = _useState12[1];
  var _useState13 = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)(props.settings.defaultExpireOffset),
    _useState14 = _slicedToArray(_useState13, 2),
    expireOffset = _useState14[0],
    setExpireOffset = _useState14[1];
  var _useState15 = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)(props.settings.emailNotification),
    _useState16 = _slicedToArray(_useState15, 2),
    emailNotification = _useState16[0],
    setEmailNotification = _useState16[1];
  var _useState17 = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)(props.settings.autoEnabled),
    _useState18 = _slicedToArray(_useState17, 2),
    isAutoEnabled = _useState18[0],
    setIsAutoEnabled = _useState18[1];
  var _useState19 = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)(true),
    _useState20 = _slicedToArray(_useState19, 2),
    hasValidData = _useState20[0],
    setHasValidData = _useState20[1];
  var _useState21 = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)(''),
    _useState22 = _slicedToArray(_useState21, 2),
    validationError = _useState22[0],
    setValidationError = _useState22[1];
  var _useState23 = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)(''),
    _useState24 = _slicedToArray(_useState23, 2),
    taxonomyLabel = _useState24[0],
    setTaxonomyLabel = _useState24[1];
  var _useState25 = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)(originalExpireTypeList),
    _useState26 = _slicedToArray(_useState25, 2),
    howToExpireList = _useState26[0],
    setHowToExpireList = _useState26[1];
  var _useState27 = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)(props.settings.newStatus),
    _useState28 = _slicedToArray(_useState27, 2),
    newStatus = _useState28[0],
    setNewStatus = _useState28[1];
  var _useState29 = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)(false),
    _useState30 = _slicedToArray(_useState29, 2),
    hasPendingValidation = _useState30[0],
    setHasPendingValidation = _useState30[1];
  var offset = expireOffset ? expireOffset : props.settings.globalDefaultExpireOffset;
  var isPro = props.isPro != "" && props.isPro === "1";
  var HelpText = function HelpText(props) {
    return /*#__PURE__*/React.createElement("p", {
      className: "description"
    }, props.children);
  };
  var FieldRow = function FieldRow(props) {
    var className = 'publishpress-settings-field-row';
    if (props.className) {
      className += ' ' + props.className;
    }
    return /*#__PURE__*/React.createElement("div", {
      className: className
    }, props.children);
  };
  var taxonomyRelatedActions = ['category', 'category-add', 'category-remove', 'category-remove-all'];
  var onChangeTaxonomy = function onChangeTaxonomy(value) {
    setPostTypeTaxonomy(value);
  };
  var onChangeTerms = function onChangeTerms(value) {
    setSelectedTerms(value);
  };
  var onChangeHowToExpire = function onChangeHowToExpire(value) {
    setSettingHowToExpire(value);
  };
  var onChangeActive = function onChangeActive(value) {
    setIsActive(value);
  };
  var onChangeExpireOffset = function onChangeExpireOffset(value) {
    setExpireOffset(value);
  };
  var onChangeEmailNotification = function onChangeEmailNotification(value) {
    setEmailNotification(value);
  };
  var onChangeAutoEnabled = function onChangeAutoEnabled(value) {
    setIsAutoEnabled(value);
  };
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useEffect)(function () {
    // Remove items from expireTypeList if related to taxonomies and there is no taxonmoy for the post type
    if (props.taxonomiesList.length === 0) {
      var newExpireTypeList = [];
      newExpireTypeList = howToExpireList.filter(function (item) {
        return taxonomyRelatedActions.indexOf(item.value) === -1;
      });
      setHowToExpireList(newExpireTypeList);
    }
  }, []);
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useEffect)(function () {
    if (!postTypeTaxonomy || !props.taxonomiesList) {
      return;
    }
    setTermsSelectIsLoading(true);
    apiFetch({
      path: (0,_wordpress_url__WEBPACK_IMPORTED_MODULE_2__.addQueryArgs)("publishpress-future/v1/terms/".concat(postTypeTaxonomy))
    }).then(function (result) {
      var options = [];
      var settingsTermsOptions = null;
      var option;
      if (result.terms && Array.isArray(result.terms)) {
        result.terms.forEach(function (term) {
          option = {
            value: term.id,
            label: term.name
          };
          options.push(option);
          if (postTypeTaxonomy === props.settings.taxonomy && props.settings.terms.includes(term.id)) {
            if (settingsTermsOptions === null) {
              settingsTermsOptions = [];
            }
            settingsTermsOptions.push(option.label);
          }
        });
      }
      setTermOptions(options);
      setSelectedTerms(settingsTermsOptions);
      setTermsSelectIsLoading(false);
    });
    props.taxonomiesList.forEach(function (taxonomy) {
      if (taxonomy.value === postTypeTaxonomy) {
        setTaxonomyLabel(taxonomy.label);
      }
    });
  }, [postTypeTaxonomy]);
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useEffect)(function () {
    if (!taxonomyLabel) {
      return;
    }

    // Update the list of actions replacing the taxonomy name.
    var newExpireTypeList = [];
    originalExpireTypeList.forEach(function (expireType) {
      var label = expireType.label;
      if (taxonomyRelatedActions.indexOf(expireType.value) !== -1) {
        label = label.replace('%s', taxonomyLabel.toLowerCase());
      }
      newExpireTypeList.push({
        value: expireType.value,
        label: label
      });
    });
    setHowToExpireList(newExpireTypeList);
  }, [taxonomyLabel]);
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useEffect)(function () {
    if (hasValidData && props.onDataIsValid) {
      props.onDataIsValid(props.postType);
    }
    if (!hasValidData && props.onDataIsInvalid) {
      props.onDataIsInvalid(props.postType);
    }
  }, [hasValidData]);
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useEffect)(function () {
    if (hasPendingValidation && props.onValidationStarted) {
      props.onValidationStarted(props.postType);
    }
    if (!hasPendingValidation && props.onValidationFinished) {
      props.onValidationFinished(props.postType);
    }
  }, [hasPendingValidation]);
  var termOptionsLabels = termOptions.map(function (term) {
    return term.label;
  });
  var settingsRows = [/*#__PURE__*/React.createElement(___WEBPACK_IMPORTED_MODULE_0__.SettingRow, {
    label: props.text.fieldActive,
    key: 'expirationdate_activemeta-' + props.postType
  }, /*#__PURE__*/React.createElement(___WEBPACK_IMPORTED_MODULE_0__.CheckboxControl, {
    name: 'expirationdate_activemeta-' + props.postType,
    checked: isActive || false,
    label: props.text.fieldActiveLabel,
    onChange: onChangeActive
  }))];
  if (isActive) {
    settingsRows.push( /*#__PURE__*/React.createElement(___WEBPACK_IMPORTED_MODULE_0__.SettingRow, {
      label: props.text.fieldAutoEnable,
      key: 'expirationdate_autoenable-' + props.postType
    }, /*#__PURE__*/React.createElement(___WEBPACK_IMPORTED_MODULE_0__.CheckboxControl, {
      name: 'expirationdate_autoenable-' + props.postType,
      checked: isAutoEnabled || false,
      label: props.text.fieldAutoEnableLabel,
      onChange: onChangeAutoEnabled
    })));
    if (isAutoEnabled) {
      settingsRows.push( /*#__PURE__*/React.createElement(___WEBPACK_IMPORTED_MODULE_0__.SettingRow, {
        label: props.text.fieldTaxonomy,
        key: 'expirationdate_taxonomy-' + props.postType
      }, /*#__PURE__*/React.createElement(___WEBPACK_IMPORTED_MODULE_0__.SelectControl, {
        name: 'expirationdate_taxonomy-' + props.postType,
        options: props.taxonomiesList,
        selected: postTypeTaxonomy,
        noItemFoundMessage: props.text.noItemsfound,
        description: props.text.fieldTaxonomyDescription,
        data: props.postType,
        onChange: onChangeTaxonomy
      })));
      settingsRows.push( /*#__PURE__*/React.createElement(___WEBPACK_IMPORTED_MODULE_0__.SettingRow, {
        label: props.text.fieldHowToExpire,
        key: 'expirationdate_expiretype-' + props.postType
      }, /*#__PURE__*/React.createElement(___WEBPACK_IMPORTED_MODULE_0__.SelectControl, {
        name: 'expirationdate_expiretype-' + props.postType,
        className: 'pe-howtoexpire',
        options: howToExpireList,
        description: props.text.fieldHowToExpireDescription,
        selected: settingHowToExpire,
        onChange: onChangeHowToExpire
      }), settingHowToExpire === 'change-status' && /*#__PURE__*/React.createElement(___WEBPACK_IMPORTED_MODULE_0__.SelectControl, {
        name: 'expirationdate_newstatus-' + props.postType,
        options: props.statusesList,
        selected: newStatus,
        onChange: setNewStatus
      }), props.taxonomiesList.length > 0 && ['category', 'category-add', 'category-remove'].indexOf(settingHowToExpire) > -1 && /*#__PURE__*/React.createElement(___WEBPACK_IMPORTED_MODULE_0__.TokensControl, {
        label: props.text.fieldTerm,
        name: 'expirationdate_terms-' + props.postType,
        options: termOptionsLabels,
        value: selectedTerms,
        isLoading: termsSelectIsLoading,
        onChange: onChangeTerms,
        description: props.text.fieldTermDescription,
        maxSuggestions: 1000,
        expandOnFocus: true,
        autoSelectFirstMatch: true
      })));
      settingsRows.push( /*#__PURE__*/React.createElement(___WEBPACK_IMPORTED_MODULE_0__.SettingRow, {
        label: props.text.fieldDefaultDateTimeOffset,
        key: 'expired-custom-date-' + props.postType
      }, /*#__PURE__*/React.createElement(___WEBPACK_IMPORTED_MODULE_0__.TextControl, {
        name: 'expired-custom-date-' + props.postType,
        value: expireOffset,
        loading: hasPendingValidation,
        placeholder: props.settings.globalDefaultExpireOffset,
        description: props.text.fieldDefaultDateTimeOffsetDescription,
        unescapedDescription: true,
        onChange: onChangeExpireOffset
      }), /*#__PURE__*/React.createElement(_DateOffsetPreview__WEBPACK_IMPORTED_MODULE_4__["default"], {
        offset: offset,
        label: props.text.datePreview,
        labelDatePreview: props.text.datePreviewCurrent,
        labelOffsetPreview: props.text.datePreviewComputed,
        setValidationErrorCallback: setValidationError,
        setHasPendingValidationCallback: setHasPendingValidation,
        setHasValidDataCallback: setHasValidData
      })));
      settingsRows.push( /*#__PURE__*/React.createElement(___WEBPACK_IMPORTED_MODULE_0__.SettingRow, {
        label: props.text.fieldWhoToNotify,
        key: 'expirationdate_emailnotification-' + props.postType
      }, /*#__PURE__*/React.createElement(___WEBPACK_IMPORTED_MODULE_0__.TextControl, {
        name: 'expirationdate_emailnotification-' + props.postType,
        className: "large-text",
        value: emailNotification,
        description: props.text.fieldWhoToNotifyDescription,
        onChange: onChangeEmailNotification
      })));
    }

    // Add promotional fields for non-pro users
    if (!isPro) {
      // Custom statuses promotional field
      settingsRows.push( /*#__PURE__*/React.createElement(___WEBPACK_IMPORTED_MODULE_0__.SettingRow, {
        label: props.text.fieldCustomStatuses,
        key: 'custom-statuses_promo'
      }, /*#__PURE__*/React.createElement(FieldRow, null, /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("input", {
        type: "checkbox",
        disabled: true
      }), props.text.fieldCustomStatusesLabel, /*#__PURE__*/React.createElement(_wordpress_components__WEBPACK_IMPORTED_MODULE_5__.Tooltip, {
        text: props.text.proFeatureTooltip
      }, /*#__PURE__*/React.createElement("span", {
        className: "dashicons dashicons-lock pp-pro-loc-icon"
      })), /*#__PURE__*/React.createElement(HelpText, null, props.text.fieldCustomStatusesDescription)))));

      // Metadata scheduling promotional field
      settingsRows.push( /*#__PURE__*/React.createElement(___WEBPACK_IMPORTED_MODULE_0__.SettingRow, {
        label: props.text.fieldMetadataScheduling,
        key: 'metadata_mapping_promo'
      }, /*#__PURE__*/React.createElement(FieldRow, null, /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("input", {
        type: "checkbox",
        disabled: true
      }), props.text.fieldMetadataSchedulingLabel, /*#__PURE__*/React.createElement(_wordpress_components__WEBPACK_IMPORTED_MODULE_5__.Tooltip, {
        text: props.text.proFeatureTooltip
      }, /*#__PURE__*/React.createElement("span", {
        className: "dashicons dashicons-lock pp-pro-loc-icon"
      }))), /*#__PURE__*/React.createElement(HelpText, null, props.text.fieldMetadataSchedulingDescription))));
    }
  }
  settingsRows = (0,_wordpress_hooks__WEBPACK_IMPORTED_MODULE_3__.applyFilters)('expirationdate_settings_posttype', settingsRows, props, isActive, _wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState);
  var fieldSetClassNames = props.isVisible ? 'pe-settings-fieldset' : 'pe-settings-fieldset hidden';
  return /*#__PURE__*/React.createElement("div", {
    className: fieldSetClassNames
  }, /*#__PURE__*/React.createElement("h2", null, props.postTypeLabel), /*#__PURE__*/React.createElement(___WEBPACK_IMPORTED_MODULE_0__.SettingsTable, {
    bodyChildren: settingsRows
  }), !hasValidData && /*#__PURE__*/React.createElement(PanelRow, null, /*#__PURE__*/React.createElement("div", {
    className: "publishpress-future-notice publishpress-future-notice-error"
  }, /*#__PURE__*/React.createElement("strong", null, props.text.error, ":"), " ", validationError)));
};

/***/ }),

/***/ "./assets/jsx/components/PostTypesSettingsPanels.jsx":
/*!***********************************************************!*\
  !*** ./assets/jsx/components/PostTypesSettingsPanels.jsx ***!
  \***********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   PostTypesSettingsPanels: () => (/* binding */ PostTypesSettingsPanels)
/* harmony export */ });
/* harmony import */ var ___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./ */ "./assets/jsx/components/index.jsx");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _publishpress_i18n__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @publishpress/i18n */ "@publishpress/i18n");
/* harmony import */ var _publishpress_i18n__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_publishpress_i18n__WEBPACK_IMPORTED_MODULE_3__);
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t.return && (u = t.return(), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }
/*
 * Copyright (c) 2025, Ramble Ventures
 */





var PostTypesSettingsPanels = function PostTypesSettingsPanels(props) {
  var _useState = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)(Object.keys(props.settings)[0]),
    _useState2 = _slicedToArray(_useState, 2),
    currentTab = _useState2[0],
    setCurrentTab = _useState2[1];
  var _useState3 = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)(null),
    _useState4 = _slicedToArray(_useState3, 2),
    selectedPostType = _useState4[0],
    setSelectedPostType = _useState4[1];
  var isPro = props.isPro;
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useEffect)(function () {
    // Get post type from URL on component mount
    var urlParams = new URLSearchParams(window.location.search);
    var postTypeParam = urlParams.get('post_type');
    if (postTypeParam && props.settings[postTypeParam]) {
      setSelectedPostType(postTypeParam);
      setCurrentTab(postTypeParam);
    }
  }, []);
  var panels = [];
  for (var _i = 0, _Object$entries = Object.entries(props.settings); _i < _Object$entries.length; _i++) {
    var _Object$entries$_i = _slicedToArray(_Object$entries[_i], 2),
      postType = _Object$entries$_i[0],
      postTypeSettings = _Object$entries$_i[1];
    panels.push( /*#__PURE__*/React.createElement(___WEBPACK_IMPORTED_MODULE_0__.PostTypeSettingsPanel, {
      legend: postTypeSettings.label,
      text: props.text,
      isPro: isPro,
      postType: postType,
      postTypeLabel: postTypeSettings.label,
      settings: postTypeSettings,
      expireTypeList: props.expireTypeList,
      taxonomiesList: props.taxonomiesList[postType],
      statusesList: props.statusesList[postType],
      key: "".concat(postType, "-panel"),
      onDataIsValid: props.onDataIsValid,
      onDataIsInvalid: props.onDataIsInvalid,
      onValidationStarted: props.onValidationStarted,
      onValidationFinished: props.onValidationFinished,
      isVisible: currentTab === postType
    }));
  }
  var onSelectPostType = function onSelectPostType(postType) {
    setSelectedPostType(postType);
    setCurrentTab(postType);

    // Update URL with the selected post type
    var newUrl = new URL(window.location);
    newUrl.searchParams.set('post_type', postType);
    window.history.pushState({}, '', newUrl);
  };
  var postTypeOptions = Object.keys(props.settings).map(function (postType) {
    return {
      label: props.settings[postType].label,
      value: postType
    };
  });
  return /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("div", {
    className: "pe-post-type-select"
  }, /*#__PURE__*/React.createElement(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.__experimentalHStack, {
    style: {
      justifyContent: 'flex-start',
      alignItems: 'stretch',
      background: '#fff',
      padding: '10px',
      border: '1px solid #ccc',
      marginBottom: '10px'
    }
  }, /*#__PURE__*/React.createElement("label", {
    style: {
      lineHeight: '33px'
    }
  }, (0,_publishpress_i18n__WEBPACK_IMPORTED_MODULE_3__.__)('Select a post type to edit:', 'post-expirator')), /*#__PURE__*/React.createElement(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.SelectControl, {
    value: selectedPostType,
    options: postTypeOptions,
    onChange: onSelectPostType
  }))), panels);
};

/***/ }),

/***/ "./assets/jsx/components/SelectControl.jsx":
/*!*************************************************!*\
  !*** ./assets/jsx/components/SelectControl.jsx ***!
  \*************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   SelectControl: () => (/* binding */ SelectControl)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__);
/*
 * Copyright (c) 2025, Ramble Ventures
 */


var SelectControl = function SelectControl(props) {
  var onChange = function onChange(value) {
    props.onChange(value);
  };
  return /*#__PURE__*/React.createElement(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, props.options.length === 0 && /*#__PURE__*/React.createElement("div", null, props.noItemFoundMessage), props.options.length > 0 && /*#__PURE__*/React.createElement(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.SelectControl, {
    label: props.label,
    name: props.name,
    id: props.name,
    className: props.className,
    value: props.selected,
    onChange: onChange,
    "data-data": props.data,
    options: props.options
  }), props.children, /*#__PURE__*/React.createElement("p", {
    className: "description"
  }, props.description));
};

/***/ }),

/***/ "./assets/jsx/components/SettingRow.jsx":
/*!**********************************************!*\
  !*** ./assets/jsx/components/SettingRow.jsx ***!
  \**********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   SettingRow: () => (/* binding */ SettingRow)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/*
 * Copyright (c) 2025, Ramble Ventures
 */

var SettingRow = function SettingRow(props) {
  return /*#__PURE__*/React.createElement("tr", {
    valign: "top"
  }, /*#__PURE__*/React.createElement("th", {
    scope: "row"
  }, /*#__PURE__*/React.createElement("label", {
    htmlFor: ""
  }, props.label)), /*#__PURE__*/React.createElement("td", null, props.children));
};

/***/ }),

/***/ "./assets/jsx/components/SettingsFieldset.jsx":
/*!****************************************************!*\
  !*** ./assets/jsx/components/SettingsFieldset.jsx ***!
  \****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   SettingsFieldset: () => (/* binding */ SettingsFieldset)
/* harmony export */ });
/*
 * Copyright (c) 2025, Ramble Ventures
 */

var SettingsFieldset = function SettingsFieldset(props) {
  return /*#__PURE__*/React.createElement("fieldset", {
    className: props.className
  }, /*#__PURE__*/React.createElement("legend", null, props.legend), props.children);
};

/***/ }),

/***/ "./assets/jsx/components/SettingsForm.jsx":
/*!************************************************!*\
  !*** ./assets/jsx/components/SettingsForm.jsx ***!
  \************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   SettingsForm: () => (/* binding */ SettingsForm)
/* harmony export */ });
/*
 * Copyright (c) 2025, Ramble Ventures
 */

var SettingsForm = function SettingsForm(props) {
  return /*#__PURE__*/React.createElement("form", {
    method: "post"
  }, props.children);
};

/***/ }),

/***/ "./assets/jsx/components/SettingsSection.jsx":
/*!***************************************************!*\
  !*** ./assets/jsx/components/SettingsSection.jsx ***!
  \***************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   SettingsSection: () => (/* binding */ SettingsSection)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/*
 * Copyright (c) 2025, Ramble Ventures
 */

var SettingsSection = function SettingsSection(props) {
  return /*#__PURE__*/React.createElement(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, /*#__PURE__*/React.createElement("h2", null, props.title), /*#__PURE__*/React.createElement("p", null, props.description), props.children);
};

/***/ }),

/***/ "./assets/jsx/components/SettingsTable.jsx":
/*!*************************************************!*\
  !*** ./assets/jsx/components/SettingsTable.jsx ***!
  \*************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   SettingsTable: () => (/* binding */ SettingsTable)
/* harmony export */ });
/*
 * Copyright (c) 2025, Ramble Ventures
 */

var SettingsTable = function SettingsTable(props) {
  return /*#__PURE__*/React.createElement("table", {
    className: "form-table"
  }, /*#__PURE__*/React.createElement("tbody", null, props.bodyChildren));
};

/***/ }),

/***/ "./assets/jsx/components/Spinner.jsx":
/*!*******************************************!*\
  !*** ./assets/jsx/components/Spinner.jsx ***!
  \*******************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   Spinner: () => (/* binding */ Spinner)
/* harmony export */ });
/*
 * Copyright (c) 2025, Ramble Ventures
 */
var Spinner = function Spinner(props) {
  return /*#__PURE__*/React.createElement("span", {
    className: "publishpress-future-spinner"
  }, /*#__PURE__*/React.createElement("div", null), /*#__PURE__*/React.createElement("div", null), /*#__PURE__*/React.createElement("div", null), /*#__PURE__*/React.createElement("div", null));
};

/***/ }),

/***/ "./assets/jsx/components/SubmitButton.jsx":
/*!************************************************!*\
  !*** ./assets/jsx/components/SubmitButton.jsx ***!
  \************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   SubmitButton: () => (/* binding */ SubmitButton)
/* harmony export */ });
/*
 * Copyright (c) 2025, Ramble Ventures
 */

var SubmitButton = function SubmitButton(props) {
  return /*#__PURE__*/React.createElement("input", {
    type: "submit",
    name: props.name,
    value: props.text,
    disabled: props.disabled,
    className: "button-primary"
  });
};

/***/ }),

/***/ "./assets/jsx/components/TextControl.jsx":
/*!***********************************************!*\
  !*** ./assets/jsx/components/TextControl.jsx ***!
  \***********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   TextControl: () => (/* binding */ TextControl)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var ___WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./ */ "./assets/jsx/components/index.jsx");
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../utils */ "./assets/jsx/utils.jsx");
/*
 * Copyright (c) 2025, Ramble Ventures
 */




var TextControl = function TextControl(props) {
  var description;
  if (props.unescapedDescription) {
    // If using this option, the HTML has to be escaped before injected into the JS interface.
    description = /*#__PURE__*/React.createElement("p", {
      className: "description",
      dangerouslySetInnerHTML: {
        __html: (0,_utils__WEBPACK_IMPORTED_MODULE_3__.stripTags)(props.description)
      }
    });
  } else {
    description = /*#__PURE__*/React.createElement("p", {
      className: "description"
    }, props.description);
  }
  var onChange = function onChange(value) {
    if (props.onChange) {
      props.onChange(value);
    }
  };
  var className = props.className ? props.className : '';
  if (props.loading) {
    className += ' publishpress-future-loading publishpress-future-loading-input';
  }
  return /*#__PURE__*/React.createElement(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: className
  }, /*#__PURE__*/React.createElement(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.TextControl, {
    type: "text",
    label: props.label,
    name: props.name,
    id: props.name,
    className: props.className,
    value: props.value,
    placeholder: props.placeholder,
    onChange: onChange
  }), props.loading && /*#__PURE__*/React.createElement(___WEBPACK_IMPORTED_MODULE_2__.Spinner, null), description));
};

/***/ }),

/***/ "./assets/jsx/components/ToggleArrowButton.jsx":
/*!*****************************************************!*\
  !*** ./assets/jsx/components/ToggleArrowButton.jsx ***!
  \*****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   ToggleArrowButton: () => (/* binding */ ToggleArrowButton)
/* harmony export */ });
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_0__);

var ToggleArrowButton = function ToggleArrowButton(props) {
  var onClick = function onClick() {
    if (props.onClick) {
      props.onClick();
    }
  };
  var iconExpanded = props.iconExpanded ? props.iconExpanded : 'arrow-up-alt2';
  var iconCollapsed = props.iconCollapsed ? props.iconCollapsed : 'arrow-down-alt2';
  var icon = props.isExpanded ? iconExpanded : iconCollapsed;
  var title = props.isExpanded ? props.titleExpanded : props.titleCollapsed;
  return /*#__PURE__*/React.createElement(_wordpress_components__WEBPACK_IMPORTED_MODULE_0__.Button, {
    isSmall: true,
    title: title,
    icon: icon,
    onClick: onClick,
    className: props.className
  });
};

/***/ }),

/***/ "./assets/jsx/components/ToggleCalendarDatePicker.jsx":
/*!************************************************************!*\
  !*** ./assets/jsx/components/ToggleCalendarDatePicker.jsx ***!
  \************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   ToggleCalendarDatePicker: () => (/* binding */ ToggleCalendarDatePicker)
/* harmony export */ });
/* harmony import */ var _ToggleArrowButton__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./ToggleArrowButton */ "./assets/jsx/components/ToggleArrowButton.jsx");
/* harmony import */ var _DateTimePicker__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./DateTimePicker */ "./assets/jsx/components/DateTimePicker.jsx");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_2__);



var ToggleCalendarDatePicker = function ToggleCalendarDatePicker(_ref) {
  var isExpanded = _ref.isExpanded,
    strings = _ref.strings,
    onToggleCalendar = _ref.onToggleCalendar,
    currentDate = _ref.currentDate,
    onChangeDate = _ref.onChangeDate,
    is12Hour = _ref.is12Hour,
    startOfWeek = _ref.startOfWeek;
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useEffect)(function () {
    // Move the element of the toggle button to between the time and date elements.
    var toggleButtonElement = document.querySelector('.future-action-calendar-toggle');
    if (!toggleButtonElement) {
      return;
    }
    var dateTimeElement = toggleButtonElement.nextElementSibling;
    if (!dateTimeElement) {
      return;
    }
    var timeElement = dateTimeElement.querySelector('.components-datetime__time');
    if (!timeElement) {
      return;
    }
    var dateElement = timeElement.nextSibling;
    if (!dateElement) {
      return;
    }
    dateTimeElement.insertBefore(toggleButtonElement, dateElement);
  });
  return /*#__PURE__*/React.createElement(_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.Fragment, null, /*#__PURE__*/React.createElement(_ToggleArrowButton__WEBPACK_IMPORTED_MODULE_0__.ToggleArrowButton, {
    className: "future-action-calendar-toggle",
    isExpanded: isExpanded,
    iconExpanded: "arrow-up-alt2",
    iconCollapsed: "calendar",
    titleExpanded: strings.hideCalendar,
    titleCollapsed: strings.showCalendar,
    onClick: onToggleCalendar
  }), /*#__PURE__*/React.createElement(_DateTimePicker__WEBPACK_IMPORTED_MODULE_1__.DateTimePicker, {
    currentDate: currentDate,
    onChange: onChangeDate,
    __nextRemoveHelpButton: true,
    is12Hour: is12Hour,
    startOfWeek: startOfWeek
  }));
};

/***/ }),

/***/ "./assets/jsx/components/TokensControl.jsx":
/*!*************************************************!*\
  !*** ./assets/jsx/components/TokensControl.jsx ***!
  \*************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   TokensControl: () => (/* binding */ TokensControl)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../utils */ "./assets/jsx/utils.jsx");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t.return && (u = t.return(), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }
/*
 * Copyright (c) 2025, Ramble Ventures
 */



var TokensControl = function TokensControl(props) {
  var _useState = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(''),
    _useState2 = _slicedToArray(_useState, 2),
    stringValue = _useState2[0],
    setStringValue = _useState2[1];
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(function () {
    if (props.value) {
      setStringValue(props.value.join(','));
    }
  }, [props.value]);
  var description;
  if (props.description) {
    if (props.unescapedDescription) {
      // If using this option, the HTML has to be escaped before injected into the JS interface.
      description = /*#__PURE__*/React.createElement("p", {
        className: "description",
        dangerouslySetInnerHTML: {
          __html: (0,_utils__WEBPACK_IMPORTED_MODULE_2__.stripTags)(props.description)
        }
      });
    } else {
      description = /*#__PURE__*/React.createElement("p", {
        className: "description"
      }, props.description);
    }
  }
  var onChange = function onChange(value) {
    if (props.onChange) {
      props.onChange(value);
    }
    if (_typeof(value) === 'object') {
      setStringValue(value.join(','));
    } else {
      setStringValue('');
    }
  };
  var value = props.value ? props.value : [];
  return /*#__PURE__*/React.createElement(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, /*#__PURE__*/React.createElement(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.FormTokenField, {
    label: props.label,
    value: value,
    suggestions: props.options,
    onChange: onChange,
    maxSuggestions: props.maxSuggestions,
    className: "publishpres-future-token-field",
    __experimentalExpandOnFocus: props.expandOnFocus,
    __experimentalAutoSelectFirstMatch: props.autoSelectFirstMatch
  }), /*#__PURE__*/React.createElement("input", {
    type: "hidden",
    name: props.name,
    value: stringValue
  }), description);
};

/***/ }),

/***/ "./assets/jsx/components/TrueFalseControl.jsx":
/*!****************************************************!*\
  !*** ./assets/jsx/components/TrueFalseControl.jsx ***!
  \****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   TrueFalseControl: () => (/* binding */ TrueFalseControl)
/* harmony export */ });
/*
 * Copyright (c) 2025, Ramble Ventures
 */

var TrueFalseControl = function TrueFalseControl(props) {
  var Fragment = wp.element.Fragment;
  var onChange = function onChange(e) {
    if (props.onChange) {
      props.onChange(e.target.value === props.trueValue && jQuery(e.target).is(':checked'));
      // Check only the true radio... using the field name? or directly the ID
    }
  };
  return /*#__PURE__*/React.createElement(Fragment, null, /*#__PURE__*/React.createElement("input", {
    type: "radio",
    name: props.name,
    id: props.name + '-true',
    value: props.trueValue,
    defaultChecked: props.selected,
    onChange: onChange
  }), /*#__PURE__*/React.createElement("label", {
    htmlFor: props.name + '-true'
  }, props.trueLabel), "\xA0\xA0", /*#__PURE__*/React.createElement("input", {
    type: "radio",
    name: props.name,
    defaultChecked: !props.selected,
    id: props.name + '-false',
    value: props.falseValue,
    onChange: onChange
  }), /*#__PURE__*/React.createElement("label", {
    htmlFor: props.name + '-false'
  }, props.falseLabel), /*#__PURE__*/React.createElement("p", {
    className: "description"
  }, props.description));
};

/***/ }),

/***/ "./assets/jsx/components/index.jsx":
/*!*****************************************!*\
  !*** ./assets/jsx/components/index.jsx ***!
  \*****************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   ButtonsPanel: () => (/* reexport safe */ _ButtonsPanel__WEBPACK_IMPORTED_MODULE_0__.ButtonsPanel),
/* harmony export */   CheckboxControl: () => (/* reexport safe */ _CheckboxControl__WEBPACK_IMPORTED_MODULE_14__.CheckboxControl),
/* harmony export */   DateOffsetPreview: () => (/* reexport safe */ _DateOffsetPreview__WEBPACK_IMPORTED_MODULE_21__.DateOffsetPreview),
/* harmony export */   FutureActionPanel: () => (/* reexport safe */ _FutureActionPanel__WEBPACK_IMPORTED_MODULE_1__.FutureActionPanel),
/* harmony export */   FutureActionPanelBlockEditor: () => (/* reexport safe */ _FutureActionPanelBlockEditor__WEBPACK_IMPORTED_MODULE_2__.FutureActionPanelBlockEditor),
/* harmony export */   FutureActionPanelBulkEdit: () => (/* reexport safe */ _FutureActionPanelBulkEdit__WEBPACK_IMPORTED_MODULE_5__.FutureActionPanelBulkEdit),
/* harmony export */   FutureActionPanelClassicEditor: () => (/* reexport safe */ _FutureActionPanelClassicEditor__WEBPACK_IMPORTED_MODULE_3__.FutureActionPanelClassicEditor),
/* harmony export */   FutureActionPanelQuickEdit: () => (/* reexport safe */ _FutureActionPanelQuickEdit__WEBPACK_IMPORTED_MODULE_4__.FutureActionPanelQuickEdit),
/* harmony export */   NonceControl: () => (/* reexport safe */ _NonceControl__WEBPACK_IMPORTED_MODULE_18__.NonceControl),
/* harmony export */   PostTypeSettingsPanel: () => (/* reexport safe */ _PostTypeSettingsPanel__WEBPACK_IMPORTED_MODULE_6__.PostTypeSettingsPanel),
/* harmony export */   PostTypesSettingsPanels: () => (/* reexport safe */ _PostTypesSettingsPanels__WEBPACK_IMPORTED_MODULE_7__.PostTypesSettingsPanels),
/* harmony export */   SelectControl: () => (/* reexport safe */ _SelectControl__WEBPACK_IMPORTED_MODULE_15__.SelectControl),
/* harmony export */   SettingRow: () => (/* reexport safe */ _SettingRow__WEBPACK_IMPORTED_MODULE_8__.SettingRow),
/* harmony export */   SettingsFieldset: () => (/* reexport safe */ _SettingsFieldset__WEBPACK_IMPORTED_MODULE_9__.SettingsFieldset),
/* harmony export */   SettingsForm: () => (/* reexport safe */ _SettingsForm__WEBPACK_IMPORTED_MODULE_10__.SettingsForm),
/* harmony export */   SettingsSection: () => (/* reexport safe */ _SettingsSection__WEBPACK_IMPORTED_MODULE_11__.SettingsSection),
/* harmony export */   SettingsTable: () => (/* reexport safe */ _SettingsTable__WEBPACK_IMPORTED_MODULE_12__.SettingsTable),
/* harmony export */   Spinner: () => (/* reexport safe */ _Spinner__WEBPACK_IMPORTED_MODULE_20__.Spinner),
/* harmony export */   SubmitButton: () => (/* reexport safe */ _SubmitButton__WEBPACK_IMPORTED_MODULE_13__.SubmitButton),
/* harmony export */   TextControl: () => (/* reexport safe */ _TextControl__WEBPACK_IMPORTED_MODULE_16__.TextControl),
/* harmony export */   TokensControl: () => (/* reexport safe */ _TokensControl__WEBPACK_IMPORTED_MODULE_17__.TokensControl),
/* harmony export */   TrueFalseControl: () => (/* reexport safe */ _TrueFalseControl__WEBPACK_IMPORTED_MODULE_19__.TrueFalseControl)
/* harmony export */ });
/* harmony import */ var _ButtonsPanel__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./ButtonsPanel */ "./assets/jsx/components/ButtonsPanel.jsx");
/* harmony import */ var _FutureActionPanel__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./FutureActionPanel */ "./assets/jsx/components/FutureActionPanel.jsx");
/* harmony import */ var _FutureActionPanelBlockEditor__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./FutureActionPanelBlockEditor */ "./assets/jsx/components/FutureActionPanelBlockEditor.jsx");
/* harmony import */ var _FutureActionPanelClassicEditor__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./FutureActionPanelClassicEditor */ "./assets/jsx/components/FutureActionPanelClassicEditor.jsx");
/* harmony import */ var _FutureActionPanelQuickEdit__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./FutureActionPanelQuickEdit */ "./assets/jsx/components/FutureActionPanelQuickEdit.jsx");
/* harmony import */ var _FutureActionPanelBulkEdit__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./FutureActionPanelBulkEdit */ "./assets/jsx/components/FutureActionPanelBulkEdit.jsx");
/* harmony import */ var _PostTypeSettingsPanel__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./PostTypeSettingsPanel */ "./assets/jsx/components/PostTypeSettingsPanel.jsx");
/* harmony import */ var _PostTypesSettingsPanels__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./PostTypesSettingsPanels */ "./assets/jsx/components/PostTypesSettingsPanels.jsx");
/* harmony import */ var _SettingRow__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./SettingRow */ "./assets/jsx/components/SettingRow.jsx");
/* harmony import */ var _SettingsFieldset__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! ./SettingsFieldset */ "./assets/jsx/components/SettingsFieldset.jsx");
/* harmony import */ var _SettingsForm__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! ./SettingsForm */ "./assets/jsx/components/SettingsForm.jsx");
/* harmony import */ var _SettingsSection__WEBPACK_IMPORTED_MODULE_11__ = __webpack_require__(/*! ./SettingsSection */ "./assets/jsx/components/SettingsSection.jsx");
/* harmony import */ var _SettingsTable__WEBPACK_IMPORTED_MODULE_12__ = __webpack_require__(/*! ./SettingsTable */ "./assets/jsx/components/SettingsTable.jsx");
/* harmony import */ var _SubmitButton__WEBPACK_IMPORTED_MODULE_13__ = __webpack_require__(/*! ./SubmitButton */ "./assets/jsx/components/SubmitButton.jsx");
/* harmony import */ var _CheckboxControl__WEBPACK_IMPORTED_MODULE_14__ = __webpack_require__(/*! ./CheckboxControl */ "./assets/jsx/components/CheckboxControl.jsx");
/* harmony import */ var _SelectControl__WEBPACK_IMPORTED_MODULE_15__ = __webpack_require__(/*! ./SelectControl */ "./assets/jsx/components/SelectControl.jsx");
/* harmony import */ var _TextControl__WEBPACK_IMPORTED_MODULE_16__ = __webpack_require__(/*! ./TextControl */ "./assets/jsx/components/TextControl.jsx");
/* harmony import */ var _TokensControl__WEBPACK_IMPORTED_MODULE_17__ = __webpack_require__(/*! ./TokensControl */ "./assets/jsx/components/TokensControl.jsx");
/* harmony import */ var _NonceControl__WEBPACK_IMPORTED_MODULE_18__ = __webpack_require__(/*! ./NonceControl */ "./assets/jsx/components/NonceControl.jsx");
/* harmony import */ var _TrueFalseControl__WEBPACK_IMPORTED_MODULE_19__ = __webpack_require__(/*! ./TrueFalseControl */ "./assets/jsx/components/TrueFalseControl.jsx");
/* harmony import */ var _Spinner__WEBPACK_IMPORTED_MODULE_20__ = __webpack_require__(/*! ./Spinner */ "./assets/jsx/components/Spinner.jsx");
/* harmony import */ var _DateOffsetPreview__WEBPACK_IMPORTED_MODULE_21__ = __webpack_require__(/*! ./DateOffsetPreview */ "./assets/jsx/components/DateOffsetPreview.jsx");























/***/ }),

/***/ "./assets/jsx/data.jsx":
/*!*****************************!*\
  !*** ./assets/jsx/data.jsx ***!
  \*****************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   createStore: () => (/* binding */ createStore)
/* harmony export */ });
/* harmony import */ var _time__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./time */ "./assets/jsx/time.jsx");
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./utils */ "./assets/jsx/utils.jsx");
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/data */ "@wordpress/data");
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_data__WEBPACK_IMPORTED_MODULE_2__);
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }



var createStore = function createStore(props) {
  if (props.defaultState.terms && typeof props.defaultState.terms === 'string') {
    props.defaultState.terms = props.defaultState.terms.split(',').map(function (term) {
      return parseInt(term);
    });
  }
  var defaultState = {
    postId: props.defaultState.postId ? props.defaultState.postId : 0,
    action: props.defaultState.action,
    date: props.defaultState.date ? props.defaultState.date : (0,_time__WEBPACK_IMPORTED_MODULE_0__.getCurrentTimeAsTimestamp)(),
    enabled: props.defaultState.autoEnable,
    terms: props.defaultState.terms ? props.defaultState.terms : [],
    taxonomy: props.defaultState.taxonomy ? props.defaultState.taxonomy : null,
    newStatus: props.defaultState.newStatus ? props.defaultState.newStatus : 'draft',
    termsListByName: null,
    termsListById: null,
    taxonomyName: null,
    isFetchingTerms: false,
    changeAction: 'no-change',
    calendarIsVisible: true,
    hasValidData: true,
    extraData: props.defaultState.extraData ? props.defaultState.extraData : {}
  };
  var store = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_2__.createReduxStore)(props.name, {
    reducer: function reducer() {
      var state = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : defaultState;
      var action = arguments.length > 1 ? arguments[1] : undefined;
      switch (action.type) {
        case 'SET_POST_ID':
          return _objectSpread(_objectSpread({}, state), {}, {
            postId: action.postId
          });
        case 'SET_ACTION':
          return _objectSpread(_objectSpread({}, state), {}, {
            action: action.action
          });
        case 'SET_NEW_STATUS':
          return _objectSpread(_objectSpread({}, state), {}, {
            newStatus: action.newStatus
          });
        case 'SET_DATE':
          // Make sure the date is a number, if it is a string with only numbers
          if (typeof action.date !== 'number' && (0,_utils__WEBPACK_IMPORTED_MODULE_1__.isNumber)(action.date)) {
            action.date = parseInt(action.date);
          }

          // If string, convert to unix time
          if (typeof action.date === 'string') {
            action.date = new Date(action.date).getTime();
          }

          // Make sure the time is always in seconds
          action.date = (0,_time__WEBPACK_IMPORTED_MODULE_0__.normalizeUnixTimeToSeconds)(action.date);

          // Convert to formated string format, considering it is in the site's timezone
          action.date = (0,_time__WEBPACK_IMPORTED_MODULE_0__.formatUnixTimeToTimestamp)(action.date);
          return _objectSpread(_objectSpread({}, state), {}, {
            date: action.date
          });
        case 'SET_ENABLED':
          return _objectSpread(_objectSpread({}, state), {}, {
            enabled: action.enabled
          });
        case 'SET_TERMS':
          return _objectSpread(_objectSpread({}, state), {}, {
            terms: action.terms
          });
        case 'SET_TAXONOMY':
          return _objectSpread(_objectSpread({}, state), {}, {
            taxonomy: action.taxonomy
          });
        case 'SET_TERMS_LIST_BY_NAME':
          return _objectSpread(_objectSpread({}, state), {}, {
            termsListByName: action.termsListByName
          });
        case 'SET_TERMS_LIST_BY_ID':
          return _objectSpread(_objectSpread({}, state), {}, {
            termsListById: action.termsListById
          });
        case 'SET_TAXONOMY_NAME':
          return _objectSpread(_objectSpread({}, state), {}, {
            taxonomyName: action.taxonomyName
          });
        case 'SET_CHANGE_ACTION':
          return _objectSpread(_objectSpread({}, state), {}, {
            changeAction: action.changeAction
          });
        case 'SET_CALENDAR_IS_VISIBLE':
          return _objectSpread(_objectSpread({}, state), {}, {
            calendarIsVisible: action.calendarIsVisible
          });
        case 'SET_HAS_VALID_DATA':
          return _objectSpread(_objectSpread({}, state), {}, {
            hasValidData: action.hasValidData
          });
        case 'SET_EXTRA_DATA':
          return _objectSpread(_objectSpread({}, state), {}, {
            extraData: _objectSpread({}, action.extraData)
          });
        case 'SET_EXTRA_DATA_BY_NAME':
          var extraData = _objectSpread(_objectSpread({}, state.extraData), {}, _defineProperty({}, action.name, action.value));
          return _objectSpread(_objectSpread({}, state), {}, {
            extraData: _objectSpread({}, extraData)
          });
      }
      return state;
    },
    actions: {
      setPostId: function setPostId(postId) {
        return {
          type: 'SET_POST_ID',
          postId: postId
        };
      },
      setAction: function setAction(action) {
        return {
          type: 'SET_ACTION',
          action: action
        };
      },
      setNewStatus: function setNewStatus(newStatus) {
        return {
          type: 'SET_NEW_STATUS',
          newStatus: newStatus
        };
      },
      setDate: function setDate(date) {
        return {
          type: 'SET_DATE',
          date: date
        };
      },
      setEnabled: function setEnabled(enabled) {
        return {
          type: 'SET_ENABLED',
          enabled: enabled
        };
      },
      setTerms: function setTerms(terms) {
        return {
          type: 'SET_TERMS',
          terms: terms
        };
      },
      setTaxonomy: function setTaxonomy(taxonomy) {
        return {
          type: 'SET_TAXONOMY',
          taxonomy: taxonomy
        };
      },
      setTermsListByName: function setTermsListByName(termsListByName) {
        return {
          type: 'SET_TERMS_LIST_BY_NAME',
          termsListByName: termsListByName
        };
      },
      setTermsListById: function setTermsListById(termsListById) {
        return {
          type: 'SET_TERMS_LIST_BY_ID',
          termsListById: termsListById
        };
      },
      setTaxonomyName: function setTaxonomyName(taxonomyName) {
        return {
          type: 'SET_TAXONOMY_NAME',
          taxonomyName: taxonomyName
        };
      },
      setIsFetchingTerms: function setIsFetchingTerms(isFetchingTerms) {
        return {
          type: 'SET_IS_FETCHING_TERMS',
          isFetchingTerms: isFetchingTerms
        };
      },
      setChangeAction: function setChangeAction(changeAction) {
        return {
          type: 'SET_CHANGE_ACTION',
          changeAction: changeAction
        };
      },
      setCalendarIsVisible: function setCalendarIsVisible(calendarIsVisible) {
        return {
          type: 'SET_CALENDAR_IS_VISIBLE',
          calendarIsVisible: calendarIsVisible
        };
      },
      setHasValidData: function setHasValidData(hasValidData) {
        return {
          type: 'SET_HAS_VALID_DATA',
          hasValidData: hasValidData
        };
      },
      setExtraData: function setExtraData(extraData) {
        return {
          type: 'SET_EXTRA_DATA',
          extraData: extraData
        };
      },
      setExtraDataByName: function setExtraDataByName(name, value) {
        return {
          type: 'SET_EXTRA_DATA_BY_NAME',
          name: name,
          value: value
        };
      }
    },
    selectors: {
      getPostId: function getPostId(state) {
        return state.postId;
      },
      getAction: function getAction(state) {
        return state.action;
      },
      getNewStatus: function getNewStatus(state) {
        return state.newStatus;
      },
      getDate: function getDate(state) {
        return state.date;
      },
      getEnabled: function getEnabled(state) {
        return state.enabled;
      },
      getTerms: function getTerms(state) {
        return state.terms;
      },
      getTaxonomy: function getTaxonomy(state) {
        return state.taxonomy;
      },
      getTermsListByName: function getTermsListByName(state) {
        return state.termsListByName;
      },
      getTermsListById: function getTermsListById(state) {
        return state.termsListById;
      },
      getTaxonomyName: function getTaxonomyName(state) {
        return state.taxonomyName;
      },
      getIsFetchingTerms: function getIsFetchingTerms(state) {
        return state.isFetchingTerms;
      },
      getChangeAction: function getChangeAction(state) {
        return state.changeAction;
      },
      getCalendarIsVisible: function getCalendarIsVisible(state) {
        return state.calendarIsVisible;
      },
      getHasValidData: function getHasValidData(state) {
        return state.hasValidData;
      },
      getExtraData: function getExtraData(state) {
        return state.extraData;
      },
      getExtraDataByName: function getExtraDataByName(state, name) {
        return state.extraData[name] || null;
      }
    }
  });
  (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_2__.register)(store);
  return store;
};

/***/ }),

/***/ "./assets/jsx/time.jsx":
/*!*****************************!*\
  !*** ./assets/jsx/time.jsx ***!
  \*****************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   formatTimestampToUnixTime: () => (/* binding */ formatTimestampToUnixTime),
/* harmony export */   formatUnixTimeToTimestamp: () => (/* binding */ formatUnixTimeToTimestamp),
/* harmony export */   getCurrentTimeAsTimestamp: () => (/* binding */ getCurrentTimeAsTimestamp),
/* harmony export */   getCurrentTimeInSeconds: () => (/* binding */ getCurrentTimeInSeconds),
/* harmony export */   normalizeUnixTimeToMilliseconds: () => (/* binding */ normalizeUnixTimeToMilliseconds),
/* harmony export */   normalizeUnixTimeToSeconds: () => (/* binding */ normalizeUnixTimeToSeconds),
/* harmony export */   timeIsInSeconds: () => (/* binding */ timeIsInSeconds)
/* harmony export */ });
var getCurrentTimeInSeconds = function getCurrentTimeInSeconds() {
  return normalizeUnixTimeToSeconds(new Date().getTime());
};
var getCurrentTimeAsTimestamp = function getCurrentTimeAsTimestamp() {
  return formatUnixTimeToTimestamp(getCurrentTimeInSeconds());
};
var formatUnixTimeToTimestamp = function formatUnixTimeToTimestamp(unixTimestamp) {
  var date = new Date(normalizeUnixTimeToSeconds(unixTimestamp));
  var year = date.getFullYear();
  var month = ("0" + (date.getMonth() + 1)).slice(-2); // Months are zero-based
  var day = ("0" + date.getDate()).slice(-2);
  var hours = ("0" + date.getHours()).slice(-2);
  var minutes = ("0" + date.getMinutes()).slice(-2);
  var seconds = ("0" + date.getSeconds()).slice(-2);
  return "".concat(year, "-").concat(month, "-").concat(day, " ").concat(hours, ":").concat(minutes, ":").concat(seconds);
};
var formatTimestampToUnixTime = function formatTimestampToUnixTime(time) {
  var date = new Date(time);
  return normalizeUnixTimeToSeconds(date.getTime());
};
var timeIsInSeconds = function timeIsInSeconds(time) {
  return parseInt(time).toString().length <= 10;
};
var normalizeUnixTimeToSeconds = function normalizeUnixTimeToSeconds(time) {
  time = parseInt(time);
  return timeIsInSeconds() ? time : time / 1000;
};
var normalizeUnixTimeToMilliseconds = function normalizeUnixTimeToMilliseconds(time) {
  time = parseInt(time);
  return timeIsInSeconds() ? time * 1000 : time;
};

/***/ }),

/***/ "./assets/jsx/utils.jsx":
/*!******************************!*\
  !*** ./assets/jsx/utils.jsx ***!
  \******************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   compact: () => (/* binding */ compact),
/* harmony export */   debugLogFactory: () => (/* binding */ debugLogFactory),
/* harmony export */   getActionSettingsFromColumnData: () => (/* binding */ getActionSettingsFromColumnData),
/* harmony export */   getElementByName: () => (/* binding */ getElementByName),
/* harmony export */   getFieldByName: () => (/* binding */ getFieldByName),
/* harmony export */   getFieldValueByName: () => (/* binding */ getFieldValueByName),
/* harmony export */   getFieldValueByNameAsArrayOfInt: () => (/* binding */ getFieldValueByNameAsArrayOfInt),
/* harmony export */   getFieldValueByNameAsBool: () => (/* binding */ getFieldValueByNameAsBool),
/* harmony export */   isGutenbergEnabled: () => (/* binding */ isGutenbergEnabled),
/* harmony export */   isNumber: () => (/* binding */ isNumber),
/* harmony export */   stripTags: () => (/* binding */ stripTags)
/* harmony export */ });
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
var compact = function compact(array) {
  if (!array) {
    return [];
  }
  if (!Array.isArray(array) && _typeof(array) === 'object') {
    array = Object.values(array);
  }
  return array.filter(function (item) {
    return item !== null && item !== undefined && item !== '';
  });
};
var debugLogFactory = function debugLogFactory(config) {
  return function (description) {
    if (console && config.isDebugEnabled) {
      var _console;
      for (var _len = arguments.length, message = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
        message[_key - 1] = arguments[_key];
      }
      (_console = console).debug.apply(_console, ['[Future]', description].concat(message));
    }
  };
};
var isGutenbergEnabled = function isGutenbergEnabled() {
  return document.body.classList.contains('block-editor-page');
};
var getElementByName = function getElementByName(name) {
  return document.getElementsByName(name)[0];
};
var getFieldByName = function getFieldByName(name, postId) {
  return document.querySelector("#the-list tr#post-".concat(postId, " .column-expirationdate input#future_action_").concat(name, "-").concat(postId));
};
var getFieldValueByName = function getFieldValueByName(name, postId) {
  var field = getFieldByName(name, postId);
  if (!field) {
    return null;
  }
  return field.value;
};
var getFieldValueByNameAsArrayOfInt = function getFieldValueByNameAsArrayOfInt(name, postId) {
  var field = getFieldByName(name, postId);
  if (!field || !field.value) {
    return [];
  }
  if (typeof field.value === 'number') {
    field.value = field.value.toString();
  }
  return field.value.split(',').map(function (term) {
    return parseInt(term);
  });
};
var getFieldValueByNameAsBool = function getFieldValueByNameAsBool(name, postId) {
  var field = getFieldByName(name, postId);
  if (!field) {
    return false;
  }
  return field.value === '1' || field.value === 'true';
};
var getActionSettingsFromColumnData = function getActionSettingsFromColumnData(postId) {
  var columnData = document.querySelector("#post-expire-column-".concat(postId));
  if (!columnData) {
    return {};
  }
  return {
    enabled: columnData.dataset.actionEnabled === '1',
    action: columnData.dataset.actionType,
    date: columnData.dataset.actionDate,
    dateUnix: columnData.dataset.actionDateUnix,
    taxonomy: columnData.dataset.actionTaxonomy,
    terms: columnData.dataset.actionTerms,
    newStatus: columnData.dataset.actionNewStatus
  };
};

/**
 * This function is used to determine if a value is a number, including strings.
 *
 * @param {*} value
 * @returns
 */
var isNumber = function isNumber(value) {
  return !isNaN(value);
};
function stripTags(string) {
  return string.replace(/<[^>]*>?/gm, '');
}

/***/ }),

/***/ "./node_modules/css-loader/dist/cjs.js!./node_modules/postcss-loader/dist/cjs.js!./assets/jsx/components/css/block-editor.css":
/*!************************************************************************************************************************************!*\
  !*** ./node_modules/css-loader/dist/cjs.js!./node_modules/postcss-loader/dist/cjs.js!./assets/jsx/components/css/block-editor.css ***!
  \************************************************************************************************************************************/
/***/ ((module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_css_loader_dist_runtime_sourceMaps_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../../../node_modules/css-loader/dist/runtime/sourceMaps.js */ "./node_modules/css-loader/dist/runtime/sourceMaps.js");
/* harmony import */ var _node_modules_css_loader_dist_runtime_sourceMaps_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_css_loader_dist_runtime_sourceMaps_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../../../../node_modules/css-loader/dist/runtime/api.js */ "./node_modules/css-loader/dist/runtime/api.js");
/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_1__);
// Imports


var ___CSS_LOADER_EXPORT___ = _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_1___default()((_node_modules_css_loader_dist_runtime_sourceMaps_js__WEBPACK_IMPORTED_MODULE_0___default()));
// Module
___CSS_LOADER_EXPORT___.push([module.id, `.future-action-enable-checkbox {
    width: 100% !important;
}
`, "",{"version":3,"sources":["webpack://./assets/jsx/components/css/block-editor.css"],"names":[],"mappings":"AAAA;IACI,sBAAsB;AAC1B","sourcesContent":[".future-action-enable-checkbox {\n    width: 100% !important;\n}\n"],"sourceRoot":""}]);
// Exports
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (___CSS_LOADER_EXPORT___);


/***/ }),

/***/ "./node_modules/css-loader/dist/cjs.js!./node_modules/postcss-loader/dist/cjs.js!./assets/jsx/components/css/dateOffsetPreview.css":
/*!*****************************************************************************************************************************************!*\
  !*** ./node_modules/css-loader/dist/cjs.js!./node_modules/postcss-loader/dist/cjs.js!./assets/jsx/components/css/dateOffsetPreview.css ***!
  \*****************************************************************************************************************************************/
/***/ ((module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_css_loader_dist_runtime_sourceMaps_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../../../node_modules/css-loader/dist/runtime/sourceMaps.js */ "./node_modules/css-loader/dist/runtime/sourceMaps.js");
/* harmony import */ var _node_modules_css_loader_dist_runtime_sourceMaps_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_css_loader_dist_runtime_sourceMaps_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../../../../node_modules/css-loader/dist/runtime/api.js */ "./node_modules/css-loader/dist/runtime/api.js");
/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_1__);
// Imports


var ___CSS_LOADER_EXPORT___ = _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_1___default()((_node_modules_css_loader_dist_runtime_sourceMaps_js__WEBPACK_IMPORTED_MODULE_0___default()));
// Module
___CSS_LOADER_EXPORT___.push([module.id, `.publishpress-future-date-preview .publishpress-future-date-preview-value {
    font-family: monospace;
    background-color: #e7e7e7;
    padding: 4px 6px;
    display: inline-block;
    min-width: 140px;
    min-height: 20px;
}

.publishpress-future-date-preview.compact .publishpress-future-date-preview-label {
    display: block;
}

.publishpress-future-date-preview.compact {
    margin-bottom: 8px;
}

.publishpress-future-date-preview.compact h4 {
    font-size: 11px;
    font-weight: 500;
    line-height: 1.4;
    text-transform: uppercase;
    display: inline-block;
    margin-bottom: calc(8px);
    padding: 0px;
    flex-shrink: 0;
    margin-right: 12px;
    max-width: 75%;
    margin-top: 0;
}

.publishpress-future-notice.publishpress-future-notice-error {
    color: #dc3232;
}
`, "",{"version":3,"sources":["webpack://./assets/jsx/components/css/dateOffsetPreview.css"],"names":[],"mappings":"AAAA;IACI,sBAAsB;IACtB,yBAAyB;IACzB,gBAAgB;IAChB,qBAAqB;IACrB,gBAAgB;IAChB,gBAAgB;AACpB;;AAEA;IACI,cAAc;AAClB;;AAEA;IACI,kBAAkB;AACtB;;AAEA;IACI,eAAe;IACf,gBAAgB;IAChB,gBAAgB;IAChB,yBAAyB;IACzB,qBAAqB;IACrB,wBAAwB;IACxB,YAAY;IACZ,cAAc;IACd,kBAAkB;IAClB,cAAc;IACd,aAAa;AACjB;;AAEA;IACI,cAAc;AAClB","sourcesContent":[".publishpress-future-date-preview .publishpress-future-date-preview-value {\n    font-family: monospace;\n    background-color: #e7e7e7;\n    padding: 4px 6px;\n    display: inline-block;\n    min-width: 140px;\n    min-height: 20px;\n}\n\n.publishpress-future-date-preview.compact .publishpress-future-date-preview-label {\n    display: block;\n}\n\n.publishpress-future-date-preview.compact {\n    margin-bottom: 8px;\n}\n\n.publishpress-future-date-preview.compact h4 {\n    font-size: 11px;\n    font-weight: 500;\n    line-height: 1.4;\n    text-transform: uppercase;\n    display: inline-block;\n    margin-bottom: calc(8px);\n    padding: 0px;\n    flex-shrink: 0;\n    margin-right: 12px;\n    max-width: 75%;\n    margin-top: 0;\n}\n\n.publishpress-future-notice.publishpress-future-notice-error {\n    color: #dc3232;\n}\n"],"sourceRoot":""}]);
// Exports
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (___CSS_LOADER_EXPORT___);


/***/ }),

/***/ "./node_modules/css-loader/dist/runtime/api.js":
/*!*****************************************************!*\
  !*** ./node_modules/css-loader/dist/runtime/api.js ***!
  \*****************************************************/
/***/ ((module) => {



/*
  MIT License http://www.opensource.org/licenses/mit-license.php
  Author Tobias Koppers @sokra
*/
module.exports = function (cssWithMappingToString) {
  var list = [];

  // return the list of modules as css string
  list.toString = function toString() {
    return this.map(function (item) {
      var content = "";
      var needLayer = typeof item[5] !== "undefined";
      if (item[4]) {
        content += "@supports (".concat(item[4], ") {");
      }
      if (item[2]) {
        content += "@media ".concat(item[2], " {");
      }
      if (needLayer) {
        content += "@layer".concat(item[5].length > 0 ? " ".concat(item[5]) : "", " {");
      }
      content += cssWithMappingToString(item);
      if (needLayer) {
        content += "}";
      }
      if (item[2]) {
        content += "}";
      }
      if (item[4]) {
        content += "}";
      }
      return content;
    }).join("");
  };

  // import a list of modules into the list
  list.i = function i(modules, media, dedupe, supports, layer) {
    if (typeof modules === "string") {
      modules = [[null, modules, undefined]];
    }
    var alreadyImportedModules = {};
    if (dedupe) {
      for (var k = 0; k < this.length; k++) {
        var id = this[k][0];
        if (id != null) {
          alreadyImportedModules[id] = true;
        }
      }
    }
    for (var _k = 0; _k < modules.length; _k++) {
      var item = [].concat(modules[_k]);
      if (dedupe && alreadyImportedModules[item[0]]) {
        continue;
      }
      if (typeof layer !== "undefined") {
        if (typeof item[5] === "undefined") {
          item[5] = layer;
        } else {
          item[1] = "@layer".concat(item[5].length > 0 ? " ".concat(item[5]) : "", " {").concat(item[1], "}");
          item[5] = layer;
        }
      }
      if (media) {
        if (!item[2]) {
          item[2] = media;
        } else {
          item[1] = "@media ".concat(item[2], " {").concat(item[1], "}");
          item[2] = media;
        }
      }
      if (supports) {
        if (!item[4]) {
          item[4] = "".concat(supports);
        } else {
          item[1] = "@supports (".concat(item[4], ") {").concat(item[1], "}");
          item[4] = supports;
        }
      }
      list.push(item);
    }
  };
  return list;
};

/***/ }),

/***/ "./node_modules/css-loader/dist/runtime/sourceMaps.js":
/*!************************************************************!*\
  !*** ./node_modules/css-loader/dist/runtime/sourceMaps.js ***!
  \************************************************************/
/***/ ((module) => {



module.exports = function (item) {
  var content = item[1];
  var cssMapping = item[3];
  if (!cssMapping) {
    return content;
  }
  if (typeof btoa === "function") {
    var base64 = btoa(unescape(encodeURIComponent(JSON.stringify(cssMapping))));
    var data = "sourceMappingURL=data:application/json;charset=utf-8;base64,".concat(base64);
    var sourceMapping = "/*# ".concat(data, " */");
    return [content].concat([sourceMapping]).join("\n");
  }
  return [content].join("\n");
};

/***/ }),

/***/ "./node_modules/react-dom/client.js":
/*!******************************************!*\
  !*** ./node_modules/react-dom/client.js ***!
  \******************************************/
/***/ ((__unused_webpack_module, exports, __webpack_require__) => {



var m = __webpack_require__(/*! react-dom */ "react-dom");
if (false) {} else {
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


/***/ }),

/***/ "./assets/jsx/components/css/block-editor.css":
/*!****************************************************!*\
  !*** ./assets/jsx/components/css/block-editor.css ***!
  \****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! !../../../../node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js */ "./node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js");
/* harmony import */ var _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _node_modules_style_loader_dist_runtime_styleDomAPI_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! !../../../../node_modules/style-loader/dist/runtime/styleDomAPI.js */ "./node_modules/style-loader/dist/runtime/styleDomAPI.js");
/* harmony import */ var _node_modules_style_loader_dist_runtime_styleDomAPI_js__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_node_modules_style_loader_dist_runtime_styleDomAPI_js__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _node_modules_style_loader_dist_runtime_insertBySelector_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! !../../../../node_modules/style-loader/dist/runtime/insertBySelector.js */ "./node_modules/style-loader/dist/runtime/insertBySelector.js");
/* harmony import */ var _node_modules_style_loader_dist_runtime_insertBySelector_js__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_node_modules_style_loader_dist_runtime_insertBySelector_js__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _node_modules_style_loader_dist_runtime_setAttributesWithoutAttributes_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! !../../../../node_modules/style-loader/dist/runtime/setAttributesWithoutAttributes.js */ "./node_modules/style-loader/dist/runtime/setAttributesWithoutAttributes.js");
/* harmony import */ var _node_modules_style_loader_dist_runtime_setAttributesWithoutAttributes_js__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_node_modules_style_loader_dist_runtime_setAttributesWithoutAttributes_js__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _node_modules_style_loader_dist_runtime_insertStyleElement_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! !../../../../node_modules/style-loader/dist/runtime/insertStyleElement.js */ "./node_modules/style-loader/dist/runtime/insertStyleElement.js");
/* harmony import */ var _node_modules_style_loader_dist_runtime_insertStyleElement_js__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_node_modules_style_loader_dist_runtime_insertStyleElement_js__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _node_modules_style_loader_dist_runtime_styleTagTransform_js__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! !../../../../node_modules/style-loader/dist/runtime/styleTagTransform.js */ "./node_modules/style-loader/dist/runtime/styleTagTransform.js");
/* harmony import */ var _node_modules_style_loader_dist_runtime_styleTagTransform_js__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(_node_modules_style_loader_dist_runtime_styleTagTransform_js__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var _node_modules_css_loader_dist_cjs_js_node_modules_postcss_loader_dist_cjs_js_block_editor_css__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! !!../../../../node_modules/css-loader/dist/cjs.js!../../../../node_modules/postcss-loader/dist/cjs.js!./block-editor.css */ "./node_modules/css-loader/dist/cjs.js!./node_modules/postcss-loader/dist/cjs.js!./assets/jsx/components/css/block-editor.css");

      
      
      
      
      
      
      
      
      

var options = {};

options.styleTagTransform = (_node_modules_style_loader_dist_runtime_styleTagTransform_js__WEBPACK_IMPORTED_MODULE_5___default());
options.setAttributes = (_node_modules_style_loader_dist_runtime_setAttributesWithoutAttributes_js__WEBPACK_IMPORTED_MODULE_3___default());
options.insert = _node_modules_style_loader_dist_runtime_insertBySelector_js__WEBPACK_IMPORTED_MODULE_2___default().bind(null, "head");
options.domAPI = (_node_modules_style_loader_dist_runtime_styleDomAPI_js__WEBPACK_IMPORTED_MODULE_1___default());
options.insertStyleElement = (_node_modules_style_loader_dist_runtime_insertStyleElement_js__WEBPACK_IMPORTED_MODULE_4___default());

var update = _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default()(_node_modules_css_loader_dist_cjs_js_node_modules_postcss_loader_dist_cjs_js_block_editor_css__WEBPACK_IMPORTED_MODULE_6__["default"], options);




       /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_css_loader_dist_cjs_js_node_modules_postcss_loader_dist_cjs_js_block_editor_css__WEBPACK_IMPORTED_MODULE_6__["default"] && _node_modules_css_loader_dist_cjs_js_node_modules_postcss_loader_dist_cjs_js_block_editor_css__WEBPACK_IMPORTED_MODULE_6__["default"].locals ? _node_modules_css_loader_dist_cjs_js_node_modules_postcss_loader_dist_cjs_js_block_editor_css__WEBPACK_IMPORTED_MODULE_6__["default"].locals : undefined);


/***/ }),

/***/ "./assets/jsx/components/css/dateOffsetPreview.css":
/*!*********************************************************!*\
  !*** ./assets/jsx/components/css/dateOffsetPreview.css ***!
  \*********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! !../../../../node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js */ "./node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js");
/* harmony import */ var _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _node_modules_style_loader_dist_runtime_styleDomAPI_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! !../../../../node_modules/style-loader/dist/runtime/styleDomAPI.js */ "./node_modules/style-loader/dist/runtime/styleDomAPI.js");
/* harmony import */ var _node_modules_style_loader_dist_runtime_styleDomAPI_js__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_node_modules_style_loader_dist_runtime_styleDomAPI_js__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _node_modules_style_loader_dist_runtime_insertBySelector_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! !../../../../node_modules/style-loader/dist/runtime/insertBySelector.js */ "./node_modules/style-loader/dist/runtime/insertBySelector.js");
/* harmony import */ var _node_modules_style_loader_dist_runtime_insertBySelector_js__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_node_modules_style_loader_dist_runtime_insertBySelector_js__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _node_modules_style_loader_dist_runtime_setAttributesWithoutAttributes_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! !../../../../node_modules/style-loader/dist/runtime/setAttributesWithoutAttributes.js */ "./node_modules/style-loader/dist/runtime/setAttributesWithoutAttributes.js");
/* harmony import */ var _node_modules_style_loader_dist_runtime_setAttributesWithoutAttributes_js__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_node_modules_style_loader_dist_runtime_setAttributesWithoutAttributes_js__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _node_modules_style_loader_dist_runtime_insertStyleElement_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! !../../../../node_modules/style-loader/dist/runtime/insertStyleElement.js */ "./node_modules/style-loader/dist/runtime/insertStyleElement.js");
/* harmony import */ var _node_modules_style_loader_dist_runtime_insertStyleElement_js__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_node_modules_style_loader_dist_runtime_insertStyleElement_js__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _node_modules_style_loader_dist_runtime_styleTagTransform_js__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! !../../../../node_modules/style-loader/dist/runtime/styleTagTransform.js */ "./node_modules/style-loader/dist/runtime/styleTagTransform.js");
/* harmony import */ var _node_modules_style_loader_dist_runtime_styleTagTransform_js__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(_node_modules_style_loader_dist_runtime_styleTagTransform_js__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var _node_modules_css_loader_dist_cjs_js_node_modules_postcss_loader_dist_cjs_js_dateOffsetPreview_css__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! !!../../../../node_modules/css-loader/dist/cjs.js!../../../../node_modules/postcss-loader/dist/cjs.js!./dateOffsetPreview.css */ "./node_modules/css-loader/dist/cjs.js!./node_modules/postcss-loader/dist/cjs.js!./assets/jsx/components/css/dateOffsetPreview.css");

      
      
      
      
      
      
      
      
      

var options = {};

options.styleTagTransform = (_node_modules_style_loader_dist_runtime_styleTagTransform_js__WEBPACK_IMPORTED_MODULE_5___default());
options.setAttributes = (_node_modules_style_loader_dist_runtime_setAttributesWithoutAttributes_js__WEBPACK_IMPORTED_MODULE_3___default());
options.insert = _node_modules_style_loader_dist_runtime_insertBySelector_js__WEBPACK_IMPORTED_MODULE_2___default().bind(null, "head");
options.domAPI = (_node_modules_style_loader_dist_runtime_styleDomAPI_js__WEBPACK_IMPORTED_MODULE_1___default());
options.insertStyleElement = (_node_modules_style_loader_dist_runtime_insertStyleElement_js__WEBPACK_IMPORTED_MODULE_4___default());

var update = _node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default()(_node_modules_css_loader_dist_cjs_js_node_modules_postcss_loader_dist_cjs_js_dateOffsetPreview_css__WEBPACK_IMPORTED_MODULE_6__["default"], options);




       /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_css_loader_dist_cjs_js_node_modules_postcss_loader_dist_cjs_js_dateOffsetPreview_css__WEBPACK_IMPORTED_MODULE_6__["default"] && _node_modules_css_loader_dist_cjs_js_node_modules_postcss_loader_dist_cjs_js_dateOffsetPreview_css__WEBPACK_IMPORTED_MODULE_6__["default"].locals ? _node_modules_css_loader_dist_cjs_js_node_modules_postcss_loader_dist_cjs_js_dateOffsetPreview_css__WEBPACK_IMPORTED_MODULE_6__["default"].locals : undefined);


/***/ }),

/***/ "./node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js":
/*!****************************************************************************!*\
  !*** ./node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js ***!
  \****************************************************************************/
/***/ ((module) => {



var stylesInDOM = [];
function getIndexByIdentifier(identifier) {
  var result = -1;
  for (var i = 0; i < stylesInDOM.length; i++) {
    if (stylesInDOM[i].identifier === identifier) {
      result = i;
      break;
    }
  }
  return result;
}
function modulesToDom(list, options) {
  var idCountMap = {};
  var identifiers = [];
  for (var i = 0; i < list.length; i++) {
    var item = list[i];
    var id = options.base ? item[0] + options.base : item[0];
    var count = idCountMap[id] || 0;
    var identifier = "".concat(id, " ").concat(count);
    idCountMap[id] = count + 1;
    var indexByIdentifier = getIndexByIdentifier(identifier);
    var obj = {
      css: item[1],
      media: item[2],
      sourceMap: item[3],
      supports: item[4],
      layer: item[5]
    };
    if (indexByIdentifier !== -1) {
      stylesInDOM[indexByIdentifier].references++;
      stylesInDOM[indexByIdentifier].updater(obj);
    } else {
      var updater = addElementStyle(obj, options);
      options.byIndex = i;
      stylesInDOM.splice(i, 0, {
        identifier: identifier,
        updater: updater,
        references: 1
      });
    }
    identifiers.push(identifier);
  }
  return identifiers;
}
function addElementStyle(obj, options) {
  var api = options.domAPI(options);
  api.update(obj);
  var updater = function updater(newObj) {
    if (newObj) {
      if (newObj.css === obj.css && newObj.media === obj.media && newObj.sourceMap === obj.sourceMap && newObj.supports === obj.supports && newObj.layer === obj.layer) {
        return;
      }
      api.update(obj = newObj);
    } else {
      api.remove();
    }
  };
  return updater;
}
module.exports = function (list, options) {
  options = options || {};
  list = list || [];
  var lastIdentifiers = modulesToDom(list, options);
  return function update(newList) {
    newList = newList || [];
    for (var i = 0; i < lastIdentifiers.length; i++) {
      var identifier = lastIdentifiers[i];
      var index = getIndexByIdentifier(identifier);
      stylesInDOM[index].references--;
    }
    var newLastIdentifiers = modulesToDom(newList, options);
    for (var _i = 0; _i < lastIdentifiers.length; _i++) {
      var _identifier = lastIdentifiers[_i];
      var _index = getIndexByIdentifier(_identifier);
      if (stylesInDOM[_index].references === 0) {
        stylesInDOM[_index].updater();
        stylesInDOM.splice(_index, 1);
      }
    }
    lastIdentifiers = newLastIdentifiers;
  };
};

/***/ }),

/***/ "./node_modules/style-loader/dist/runtime/insertBySelector.js":
/*!********************************************************************!*\
  !*** ./node_modules/style-loader/dist/runtime/insertBySelector.js ***!
  \********************************************************************/
/***/ ((module) => {



var memo = {};

/* istanbul ignore next  */
function getTarget(target) {
  if (typeof memo[target] === "undefined") {
    var styleTarget = document.querySelector(target);

    // Special case to return head of iframe instead of iframe itself
    if (window.HTMLIFrameElement && styleTarget instanceof window.HTMLIFrameElement) {
      try {
        // This will throw an exception if access to iframe is blocked
        // due to cross-origin restrictions
        styleTarget = styleTarget.contentDocument.head;
      } catch (e) {
        // istanbul ignore next
        styleTarget = null;
      }
    }
    memo[target] = styleTarget;
  }
  return memo[target];
}

/* istanbul ignore next  */
function insertBySelector(insert, style) {
  var target = getTarget(insert);
  if (!target) {
    throw new Error("Couldn't find a style target. This probably means that the value for the 'insert' parameter is invalid.");
  }
  target.appendChild(style);
}
module.exports = insertBySelector;

/***/ }),

/***/ "./node_modules/style-loader/dist/runtime/insertStyleElement.js":
/*!**********************************************************************!*\
  !*** ./node_modules/style-loader/dist/runtime/insertStyleElement.js ***!
  \**********************************************************************/
/***/ ((module) => {



/* istanbul ignore next  */
function insertStyleElement(options) {
  var element = document.createElement("style");
  options.setAttributes(element, options.attributes);
  options.insert(element, options.options);
  return element;
}
module.exports = insertStyleElement;

/***/ }),

/***/ "./node_modules/style-loader/dist/runtime/setAttributesWithoutAttributes.js":
/*!**********************************************************************************!*\
  !*** ./node_modules/style-loader/dist/runtime/setAttributesWithoutAttributes.js ***!
  \**********************************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {



/* istanbul ignore next  */
function setAttributesWithoutAttributes(styleElement) {
  var nonce =  true ? __webpack_require__.nc : 0;
  if (nonce) {
    styleElement.setAttribute("nonce", nonce);
  }
}
module.exports = setAttributesWithoutAttributes;

/***/ }),

/***/ "./node_modules/style-loader/dist/runtime/styleDomAPI.js":
/*!***************************************************************!*\
  !*** ./node_modules/style-loader/dist/runtime/styleDomAPI.js ***!
  \***************************************************************/
/***/ ((module) => {



/* istanbul ignore next  */
function apply(styleElement, options, obj) {
  var css = "";
  if (obj.supports) {
    css += "@supports (".concat(obj.supports, ") {");
  }
  if (obj.media) {
    css += "@media ".concat(obj.media, " {");
  }
  var needLayer = typeof obj.layer !== "undefined";
  if (needLayer) {
    css += "@layer".concat(obj.layer.length > 0 ? " ".concat(obj.layer) : "", " {");
  }
  css += obj.css;
  if (needLayer) {
    css += "}";
  }
  if (obj.media) {
    css += "}";
  }
  if (obj.supports) {
    css += "}";
  }
  var sourceMap = obj.sourceMap;
  if (sourceMap && typeof btoa !== "undefined") {
    css += "\n/*# sourceMappingURL=data:application/json;base64,".concat(btoa(unescape(encodeURIComponent(JSON.stringify(sourceMap)))), " */");
  }

  // For old IE
  /* istanbul ignore if  */
  options.styleTagTransform(css, styleElement, options.options);
}
function removeStyleElement(styleElement) {
  // istanbul ignore if
  if (styleElement.parentNode === null) {
    return false;
  }
  styleElement.parentNode.removeChild(styleElement);
}

/* istanbul ignore next  */
function domAPI(options) {
  if (typeof document === "undefined") {
    return {
      update: function update() {},
      remove: function remove() {}
    };
  }
  var styleElement = options.insertStyleElement(options);
  return {
    update: function update(obj) {
      apply(styleElement, options, obj);
    },
    remove: function remove() {
      removeStyleElement(styleElement);
    }
  };
}
module.exports = domAPI;

/***/ }),

/***/ "./node_modules/style-loader/dist/runtime/styleTagTransform.js":
/*!*********************************************************************!*\
  !*** ./node_modules/style-loader/dist/runtime/styleTagTransform.js ***!
  \*********************************************************************/
/***/ ((module) => {



/* istanbul ignore next  */
function styleTagTransform(css, styleElement) {
  if (styleElement.styleSheet) {
    styleElement.styleSheet.cssText = css;
  } else {
    while (styleElement.firstChild) {
      styleElement.removeChild(styleElement.firstChild);
    }
    styleElement.appendChild(document.createTextNode(css));
  }
}
module.exports = styleTagTransform;

/***/ }),

/***/ "react-dom":
/*!***************************!*\
  !*** external "ReactDOM" ***!
  \***************************/
/***/ ((module) => {

module.exports = ReactDOM;

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

/***/ }),

/***/ "@wordpress/hooks":
/*!***************************!*\
  !*** external "wp.hooks" ***!
  \***************************/
/***/ ((module) => {

module.exports = wp.hooks;

/***/ }),

/***/ "@wordpress/plugins":
/*!*****************************!*\
  !*** external "wp.plugins" ***!
  \*****************************/
/***/ ((module) => {

module.exports = wp.plugins;

/***/ }),

/***/ "@wordpress/url":
/*!*************************!*\
  !*** external "wp.url" ***!
  \*************************/
/***/ ((module) => {

module.exports = wp.url;

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
/******/ 			id: moduleId,
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
/******/ 	/* webpack/runtime/nonce */
/******/ 	(() => {
/******/ 		__webpack_require__.nc = undefined;
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be isolated against other modules in the chunk.
(() => {
/*!***********************************!*\
  !*** ./assets/jsx/quick-edit.jsx ***!
  \***********************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _components__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./components */ "./assets/jsx/components/index.jsx");
/* harmony import */ var _data__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./data */ "./assets/jsx/data.jsx");
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./utils */ "./assets/jsx/utils.jsx");
/* harmony import */ var react_dom_client__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! react-dom/client */ "./node_modules/react-dom/client.js");
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @wordpress/data */ "@wordpress/data");
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_wordpress_data__WEBPACK_IMPORTED_MODULE_4__);





var _window = window,
  inlineEditPost = _window.inlineEditPost;
var _window$publishpressF = window.publishpressFutureQuickEditConfig,
  postType = _window$publishpressF.postType,
  isNewPost = _window$publishpressF.isNewPost,
  actionsSelectOptions = _window$publishpressF.actionsSelectOptions,
  is12Hour = _window$publishpressF.is12Hour,
  timeFormat = _window$publishpressF.timeFormat,
  startOfWeek = _window$publishpressF.startOfWeek,
  strings = _window$publishpressF.strings,
  taxonomyName = _window$publishpressF.taxonomyName,
  nonce = _window$publishpressF.nonce,
  statusesSelectOptions = _window$publishpressF.statusesSelectOptions,
  hideCalendarByDefault = _window$publishpressF.hideCalendarByDefault,
  hiddenFields = _window$publishpressF.hiddenFields,
  wpTimezone = _window$publishpressF.wpTimezone;
var storeName = 'publishpress-future/future-action-quick-edit';
var delayToUnmountAfterSaving = 1000;

// We create a copy of the WP inline edit post function
var wpInlineEdit = inlineEditPost.edit;
var wpInlineEditRevert = inlineEditPost.revert;
var getPostIdFromButton = function getPostIdFromButton(id) {
  // If id is a string or a number, return it directly
  if (typeof id === 'string' || typeof id === 'number') {
    return id;
  }

  // Otherwise, assume it's an HTML element and extract the post ID
  var trElement = id.closest('tr');
  var trId = trElement.id;
  var postId = trId.split('-')[1];
  return postId;
};

/**
 * We override the function with our own code so we can detect when
 * the inline edit row is displayed to recreate the React component.
 */
inlineEditPost.edit = function (button, id) {
  // Call the original WP edit function.
  wpInlineEdit.apply(this, arguments);
  var postId = getPostIdFromButton(button);
  var data = (0,_utils__WEBPACK_IMPORTED_MODULE_2__.getActionSettingsFromColumnData)(postId);
  if (!data) {
    return;
  }
  var enabled = data.enabled;
  var action = data.action;
  var date = data.date;
  var taxonomy = data.taxonomy;
  var newStatus = data.newStatus;
  var terms = data.terms;
  if (typeof terms === 'string') {
    terms = terms.split(',');
  }

  // if store exists, update the state. Otherwise, create it.
  if ((0,_wordpress_data__WEBPACK_IMPORTED_MODULE_4__.select)(storeName)) {
    (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_4__.dispatch)(storeName).setPostId(postId);
    (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_4__.dispatch)(storeName).setEnabled(enabled);
    (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_4__.dispatch)(storeName).setAction(action);
    (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_4__.dispatch)(storeName).setDate(date);
    (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_4__.dispatch)(storeName).setTaxonomy(taxonomy);
    (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_4__.dispatch)(storeName).setTerms(terms);
    (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_4__.dispatch)(storeName).setNewStatus(newStatus);
  } else {
    (0,_data__WEBPACK_IMPORTED_MODULE_1__.createStore)({
      name: storeName,
      defaultState: {
        postId: postId,
        autoEnable: enabled,
        action: action,
        date: date,
        taxonomy: taxonomy,
        terms: terms,
        newStatus: newStatus
      }
    });
  }
  var container = document.getElementById("publishpress-future-quick-edit");
  if (!container) {
    return;
  }
  var root = (0,react_dom_client__WEBPACK_IMPORTED_MODULE_3__.createRoot)(container);
  var saveButton = document.querySelector('.inline-edit-save .save');
  if (saveButton) {
    saveButton.onclick = function () {
      setTimeout(function () {
        root.unmount();
      }, delayToUnmountAfterSaving);
    };
  }
  var component = /*#__PURE__*/React.createElement(_components__WEBPACK_IMPORTED_MODULE_0__.FutureActionPanelQuickEdit, {
    storeName: storeName,
    postType: postType,
    isNewPost: isNewPost,
    actionsSelectOptions: actionsSelectOptions,
    statusesSelectOptions: statusesSelectOptions,
    is12Hour: is12Hour,
    timeFormat: timeFormat,
    startOfWeek: startOfWeek,
    strings: strings,
    taxonomyName: taxonomyName,
    nonce: nonce,
    hideCalendarByDefault: hideCalendarByDefault,
    hiddenFields: hiddenFields,
    wpTimezone: wpTimezone
  });
  root.render(component);
  inlineEditPost.revert = function () {
    root.unmount();

    // Call the original WP revert function.
    wpInlineEditRevert.apply(this, arguments);
  };
};
})();

/******/ })()
;
//# sourceMappingURL=quickEdit.js.map