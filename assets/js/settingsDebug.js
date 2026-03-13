/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./node_modules/@wordpress/api-fetch/build-module/index.js"
/*!*****************************************************************!*\
  !*** ./node_modules/@wordpress/api-fetch/build-module/index.js ***!
  \*****************************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _middlewares_nonce__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./middlewares/nonce */ "./node_modules/@wordpress/api-fetch/build-module/middlewares/nonce.js");
/* harmony import */ var _middlewares_root_url__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./middlewares/root-url */ "./node_modules/@wordpress/api-fetch/build-module/middlewares/root-url.js");
/* harmony import */ var _middlewares_preloading__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./middlewares/preloading */ "./node_modules/@wordpress/api-fetch/build-module/middlewares/preloading.js");
/* harmony import */ var _middlewares_fetch_all_middleware__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./middlewares/fetch-all-middleware */ "./node_modules/@wordpress/api-fetch/build-module/middlewares/fetch-all-middleware.js");
/* harmony import */ var _middlewares_namespace_endpoint__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./middlewares/namespace-endpoint */ "./node_modules/@wordpress/api-fetch/build-module/middlewares/namespace-endpoint.js");
/* harmony import */ var _middlewares_http_v1__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./middlewares/http-v1 */ "./node_modules/@wordpress/api-fetch/build-module/middlewares/http-v1.js");
/* harmony import */ var _middlewares_user_locale__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./middlewares/user-locale */ "./node_modules/@wordpress/api-fetch/build-module/middlewares/user-locale.js");
/* harmony import */ var _middlewares_media_upload__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./middlewares/media-upload */ "./node_modules/@wordpress/api-fetch/build-module/middlewares/media-upload.js");
/* harmony import */ var _middlewares_theme_preview__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! ./middlewares/theme-preview */ "./node_modules/@wordpress/api-fetch/build-module/middlewares/theme-preview.js");
/* harmony import */ var _utils_response__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! ./utils/response */ "./node_modules/@wordpress/api-fetch/build-module/utils/response.js");
/**
 * WordPress dependencies
 */


/**
 * Internal dependencies
 */











/**
 * Default set of header values which should be sent with every request unless
 * explicitly provided through apiFetch options.
 *
 * @type {Record<string, string>}
 */
const DEFAULT_HEADERS = {
  // The backend uses the Accept header as a condition for considering an
  // incoming request as a REST request.
  //
  // See: https://core.trac.wordpress.org/ticket/44534
  Accept: 'application/json, */*;q=0.1'
};

/**
 * Default set of fetch option values which should be sent with every request
 * unless explicitly provided through apiFetch options.
 *
 * @type {Object}
 */
const DEFAULT_OPTIONS = {
  credentials: 'include'
};

/** @typedef {import('./types').APIFetchMiddleware} APIFetchMiddleware */
/** @typedef {import('./types').APIFetchOptions} APIFetchOptions */

/**
 * @type {import('./types').APIFetchMiddleware[]}
 */
const middlewares = [_middlewares_user_locale__WEBPACK_IMPORTED_MODULE_7__["default"], _middlewares_namespace_endpoint__WEBPACK_IMPORTED_MODULE_5__["default"], _middlewares_http_v1__WEBPACK_IMPORTED_MODULE_6__["default"], _middlewares_fetch_all_middleware__WEBPACK_IMPORTED_MODULE_4__["default"]];

/**
 * Register a middleware
 *
 * @param {import('./types').APIFetchMiddleware} middleware
 */
function registerMiddleware(middleware) {
  middlewares.unshift(middleware);
}

/**
 * Checks the status of a response, throwing the Response as an error if
 * it is outside the 200 range.
 *
 * @param {Response} response
 * @return {Response} The response if the status is in the 200 range.
 */
const checkStatus = response => {
  if (response.status >= 200 && response.status < 300) {
    return response;
  }
  throw response;
};

/** @typedef {(options: import('./types').APIFetchOptions) => Promise<any>} FetchHandler*/

/**
 * @type {FetchHandler}
 */
const defaultFetchHandler = nextOptions => {
  const {
    url,
    path,
    data,
    parse = true,
    ...remainingOptions
  } = nextOptions;
  let {
    body,
    headers
  } = nextOptions;

  // Merge explicitly-provided headers with default values.
  headers = {
    ...DEFAULT_HEADERS,
    ...headers
  };

  // The `data` property is a shorthand for sending a JSON body.
  if (data) {
    body = JSON.stringify(data);
    headers['Content-Type'] = 'application/json';
  }
  const responsePromise = window.fetch(
  // Fall back to explicitly passing `window.location` which is the behavior if `undefined` is passed.
  url || path || window.location.href, {
    ...DEFAULT_OPTIONS,
    ...remainingOptions,
    body,
    headers
  });
  return responsePromise.then(value => Promise.resolve(value).then(checkStatus).catch(response => (0,_utils_response__WEBPACK_IMPORTED_MODULE_10__.parseAndThrowError)(response, parse)).then(response => (0,_utils_response__WEBPACK_IMPORTED_MODULE_10__.parseResponseAndNormalizeError)(response, parse)), err => {
    // Re-throw AbortError for the users to handle it themselves.
    if (err && err.name === 'AbortError') {
      throw err;
    }

    // Otherwise, there is most likely no network connection.
    // Unfortunately the message might depend on the browser.
    throw {
      code: 'fetch_error',
      message: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('You are probably offline.')
    };
  });
};

/** @type {FetchHandler} */
let fetchHandler = defaultFetchHandler;

/**
 * Defines a custom fetch handler for making the requests that will override
 * the default one using window.fetch
 *
 * @param {FetchHandler} newFetchHandler The new fetch handler
 */
function setFetchHandler(newFetchHandler) {
  fetchHandler = newFetchHandler;
}

/**
 * @template T
 * @param {import('./types').APIFetchOptions} options
 * @return {Promise<T>} A promise representing the request processed via the registered middlewares.
 */
function apiFetch(options) {
  // creates a nested function chain that calls all middlewares and finally the `fetchHandler`,
  // converting `middlewares = [ m1, m2, m3 ]` into:
  // ```
  // opts1 => m1( opts1, opts2 => m2( opts2, opts3 => m3( opts3, fetchHandler ) ) );
  // ```
  const enhancedHandler = middlewares.reduceRight(( /** @type {FetchHandler} */next, middleware) => {
    return workingOptions => middleware(workingOptions, next);
  }, fetchHandler);
  return enhancedHandler(options).catch(error => {
    if (error.code !== 'rest_cookie_invalid_nonce') {
      return Promise.reject(error);
    }

    // If the nonce is invalid, refresh it and try again.
    return window
    // @ts-ignore
    .fetch(apiFetch.nonceEndpoint).then(checkStatus).then(data => data.text()).then(text => {
      // @ts-ignore
      apiFetch.nonceMiddleware.nonce = text;
      return apiFetch(options);
    });
  });
}
apiFetch.use = registerMiddleware;
apiFetch.setFetchHandler = setFetchHandler;
apiFetch.createNonceMiddleware = _middlewares_nonce__WEBPACK_IMPORTED_MODULE_1__["default"];
apiFetch.createPreloadingMiddleware = _middlewares_preloading__WEBPACK_IMPORTED_MODULE_3__["default"];
apiFetch.createRootURLMiddleware = _middlewares_root_url__WEBPACK_IMPORTED_MODULE_2__["default"];
apiFetch.fetchAllMiddleware = _middlewares_fetch_all_middleware__WEBPACK_IMPORTED_MODULE_4__["default"];
apiFetch.mediaUploadMiddleware = _middlewares_media_upload__WEBPACK_IMPORTED_MODULE_8__["default"];
apiFetch.createThemePreviewMiddleware = _middlewares_theme_preview__WEBPACK_IMPORTED_MODULE_9__["default"];
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (apiFetch);
//# sourceMappingURL=index.js.map

/***/ },

/***/ "./node_modules/@wordpress/api-fetch/build-module/middlewares/fetch-all-middleware.js"
/*!********************************************************************************************!*\
  !*** ./node_modules/@wordpress/api-fetch/build-module/middlewares/fetch-all-middleware.js ***!
  \********************************************************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _wordpress_url__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/url */ "@wordpress/url");
/* harmony import */ var _wordpress_url__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_url__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var ___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! .. */ "./node_modules/@wordpress/api-fetch/build-module/index.js");
/**
 * WordPress dependencies
 */


/**
 * Internal dependencies
 */


/**
 * Apply query arguments to both URL and Path, whichever is present.
 *
 * @param {import('../types').APIFetchOptions} props
 * @param {Record<string, string | number>}    queryArgs
 * @return {import('../types').APIFetchOptions} The request with the modified query args
 */
const modifyQuery = ({
  path,
  url,
  ...options
}, queryArgs) => ({
  ...options,
  url: url && (0,_wordpress_url__WEBPACK_IMPORTED_MODULE_0__.addQueryArgs)(url, queryArgs),
  path: path && (0,_wordpress_url__WEBPACK_IMPORTED_MODULE_0__.addQueryArgs)(path, queryArgs)
});

/**
 * Duplicates parsing functionality from apiFetch.
 *
 * @param {Response} response
 * @return {Promise<any>} Parsed response json.
 */
const parseResponse = response => response.json ? response.json() : Promise.reject(response);

/**
 * @param {string | null} linkHeader
 * @return {{ next?: string }} The parsed link header.
 */
const parseLinkHeader = linkHeader => {
  if (!linkHeader) {
    return {};
  }
  const match = linkHeader.match(/<([^>]+)>; rel="next"/);
  return match ? {
    next: match[1]
  } : {};
};

/**
 * @param {Response} response
 * @return {string | undefined} The next page URL.
 */
const getNextPageUrl = response => {
  const {
    next
  } = parseLinkHeader(response.headers.get('link'));
  return next;
};

/**
 * @param {import('../types').APIFetchOptions} options
 * @return {boolean} True if the request contains an unbounded query.
 */
const requestContainsUnboundedQuery = options => {
  const pathIsUnbounded = !!options.path && options.path.indexOf('per_page=-1') !== -1;
  const urlIsUnbounded = !!options.url && options.url.indexOf('per_page=-1') !== -1;
  return pathIsUnbounded || urlIsUnbounded;
};

/**
 * The REST API enforces an upper limit on the per_page option. To handle large
 * collections, apiFetch consumers can pass `per_page=-1`; this middleware will
 * then recursively assemble a full response array from all available pages.
 *
 * @type {import('../types').APIFetchMiddleware}
 */
const fetchAllMiddleware = async (options, next) => {
  if (options.parse === false) {
    // If a consumer has opted out of parsing, do not apply middleware.
    return next(options);
  }
  if (!requestContainsUnboundedQuery(options)) {
    // If neither url nor path is requesting all items, do not apply middleware.
    return next(options);
  }

  // Retrieve requested page of results.
  const response = await (0,___WEBPACK_IMPORTED_MODULE_1__["default"])({
    ...modifyQuery(options, {
      per_page: 100
    }),
    // Ensure headers are returned for page 1.
    parse: false
  });
  const results = await parseResponse(response);
  if (!Array.isArray(results)) {
    // We have no reliable way of merging non-array results.
    return results;
  }
  let nextPage = getNextPageUrl(response);
  if (!nextPage) {
    // There are no further pages to request.
    return results;
  }

  // Iteratively fetch all remaining pages until no "next" header is found.
  let mergedResults = /** @type {any[]} */[].concat(results);
  while (nextPage) {
    const nextResponse = await (0,___WEBPACK_IMPORTED_MODULE_1__["default"])({
      ...options,
      // Ensure the URL for the next page is used instead of any provided path.
      path: undefined,
      url: nextPage,
      // Ensure we still get headers so we can identify the next page.
      parse: false
    });
    const nextResults = await parseResponse(nextResponse);
    mergedResults = mergedResults.concat(nextResults);
    nextPage = getNextPageUrl(nextResponse);
  }
  return mergedResults;
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (fetchAllMiddleware);
//# sourceMappingURL=fetch-all-middleware.js.map

/***/ },

/***/ "./node_modules/@wordpress/api-fetch/build-module/middlewares/http-v1.js"
/*!*******************************************************************************!*\
  !*** ./node_modules/@wordpress/api-fetch/build-module/middlewares/http-v1.js ***!
  \*******************************************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/**
 * Set of HTTP methods which are eligible to be overridden.
 *
 * @type {Set<string>}
 */
const OVERRIDE_METHODS = new Set(['PATCH', 'PUT', 'DELETE']);

/**
 * Default request method.
 *
 * "A request has an associated method (a method). Unless stated otherwise it
 * is `GET`."
 *
 * @see  https://fetch.spec.whatwg.org/#requests
 *
 * @type {string}
 */
const DEFAULT_METHOD = 'GET';

/**
 * API Fetch middleware which overrides the request method for HTTP v1
 * compatibility leveraging the REST API X-HTTP-Method-Override header.
 *
 * @type {import('../types').APIFetchMiddleware}
 */
const httpV1Middleware = (options, next) => {
  const {
    method = DEFAULT_METHOD
  } = options;
  if (OVERRIDE_METHODS.has(method.toUpperCase())) {
    options = {
      ...options,
      headers: {
        ...options.headers,
        'X-HTTP-Method-Override': method,
        'Content-Type': 'application/json'
      },
      method: 'POST'
    };
  }
  return next(options);
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (httpV1Middleware);
//# sourceMappingURL=http-v1.js.map

/***/ },

/***/ "./node_modules/@wordpress/api-fetch/build-module/middlewares/media-upload.js"
/*!************************************************************************************!*\
  !*** ./node_modules/@wordpress/api-fetch/build-module/middlewares/media-upload.js ***!
  \************************************************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _utils_response__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../utils/response */ "./node_modules/@wordpress/api-fetch/build-module/utils/response.js");
/**
 * WordPress dependencies
 */


/**
 * Internal dependencies
 */


/**
 * @param {import('../types').APIFetchOptions} options
 * @return {boolean} True if the request is for media upload.
 */
function isMediaUploadRequest(options) {
  const isCreateMethod = !!options.method && options.method === 'POST';
  const isMediaEndpoint = !!options.path && options.path.indexOf('/wp/v2/media') !== -1 || !!options.url && options.url.indexOf('/wp/v2/media') !== -1;
  return isMediaEndpoint && isCreateMethod;
}

/**
 * Middleware handling media upload failures and retries.
 *
 * @type {import('../types').APIFetchMiddleware}
 */
const mediaUploadMiddleware = (options, next) => {
  if (!isMediaUploadRequest(options)) {
    return next(options);
  }
  let retries = 0;
  const maxRetries = 5;

  /**
   * @param {string} attachmentId
   * @return {Promise<any>} Processed post response.
   */
  const postProcess = attachmentId => {
    retries++;
    return next({
      path: `/wp/v2/media/${attachmentId}/post-process`,
      method: 'POST',
      data: {
        action: 'create-image-subsizes'
      },
      parse: false
    }).catch(() => {
      if (retries < maxRetries) {
        return postProcess(attachmentId);
      }
      next({
        path: `/wp/v2/media/${attachmentId}?force=true`,
        method: 'DELETE'
      });
      return Promise.reject();
    });
  };
  return next({
    ...options,
    parse: false
  }).catch(response => {
    const attachmentId = response.headers.get('x-wp-upload-attachment-id');
    if (response.status >= 500 && response.status < 600 && attachmentId) {
      return postProcess(attachmentId).catch(() => {
        if (options.parse !== false) {
          return Promise.reject({
            code: 'post_process',
            message: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Media upload failed. If this is a photo or a large image, please scale it down and try again.')
          });
        }
        return Promise.reject(response);
      });
    }
    return (0,_utils_response__WEBPACK_IMPORTED_MODULE_1__.parseAndThrowError)(response, options.parse);
  }).then(response => (0,_utils_response__WEBPACK_IMPORTED_MODULE_1__.parseResponseAndNormalizeError)(response, options.parse));
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (mediaUploadMiddleware);
//# sourceMappingURL=media-upload.js.map

/***/ },

/***/ "./node_modules/@wordpress/api-fetch/build-module/middlewares/namespace-endpoint.js"
/*!******************************************************************************************!*\
  !*** ./node_modules/@wordpress/api-fetch/build-module/middlewares/namespace-endpoint.js ***!
  \******************************************************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/**
 * @type {import('../types').APIFetchMiddleware}
 */
const namespaceAndEndpointMiddleware = (options, next) => {
  let path = options.path;
  let namespaceTrimmed, endpointTrimmed;
  if (typeof options.namespace === 'string' && typeof options.endpoint === 'string') {
    namespaceTrimmed = options.namespace.replace(/^\/|\/$/g, '');
    endpointTrimmed = options.endpoint.replace(/^\//, '');
    if (endpointTrimmed) {
      path = namespaceTrimmed + '/' + endpointTrimmed;
    } else {
      path = namespaceTrimmed;
    }
  }
  delete options.namespace;
  delete options.endpoint;
  return next({
    ...options,
    path
  });
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (namespaceAndEndpointMiddleware);
//# sourceMappingURL=namespace-endpoint.js.map

/***/ },

/***/ "./node_modules/@wordpress/api-fetch/build-module/middlewares/nonce.js"
/*!*****************************************************************************!*\
  !*** ./node_modules/@wordpress/api-fetch/build-module/middlewares/nonce.js ***!
  \*****************************************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/**
 * @param {string} nonce
 * @return {import('../types').APIFetchMiddleware & { nonce: string }} A middleware to enhance a request with a nonce.
 */
function createNonceMiddleware(nonce) {
  /**
   * @type {import('../types').APIFetchMiddleware & { nonce: string }}
   */
  const middleware = (options, next) => {
    const {
      headers = {}
    } = options;

    // If an 'X-WP-Nonce' header (or any case-insensitive variation
    // thereof) was specified, no need to add a nonce header.
    for (const headerName in headers) {
      if (headerName.toLowerCase() === 'x-wp-nonce' && headers[headerName] === middleware.nonce) {
        return next(options);
      }
    }
    return next({
      ...options,
      headers: {
        ...headers,
        'X-WP-Nonce': middleware.nonce
      }
    });
  };
  middleware.nonce = nonce;
  return middleware;
}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (createNonceMiddleware);
//# sourceMappingURL=nonce.js.map

/***/ },

/***/ "./node_modules/@wordpress/api-fetch/build-module/middlewares/preloading.js"
/*!**********************************************************************************!*\
  !*** ./node_modules/@wordpress/api-fetch/build-module/middlewares/preloading.js ***!
  \**********************************************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _wordpress_url__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/url */ "@wordpress/url");
/* harmony import */ var _wordpress_url__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_url__WEBPACK_IMPORTED_MODULE_0__);
/**
 * WordPress dependencies
 */


/**
 * @param {Record<string, any>} preloadedData
 * @return {import('../types').APIFetchMiddleware} Preloading middleware.
 */
function createPreloadingMiddleware(preloadedData) {
  const cache = Object.fromEntries(Object.entries(preloadedData).map(([path, data]) => [(0,_wordpress_url__WEBPACK_IMPORTED_MODULE_0__.normalizePath)(path), data]));
  return (options, next) => {
    const {
      parse = true
    } = options;
    /** @type {string | void} */
    let rawPath = options.path;
    if (!rawPath && options.url) {
      const {
        rest_route: pathFromQuery,
        ...queryArgs
      } = (0,_wordpress_url__WEBPACK_IMPORTED_MODULE_0__.getQueryArgs)(options.url);
      if (typeof pathFromQuery === 'string') {
        rawPath = (0,_wordpress_url__WEBPACK_IMPORTED_MODULE_0__.addQueryArgs)(pathFromQuery, queryArgs);
      }
    }
    if (typeof rawPath !== 'string') {
      return next(options);
    }
    const method = options.method || 'GET';
    const path = (0,_wordpress_url__WEBPACK_IMPORTED_MODULE_0__.normalizePath)(rawPath);
    if ('GET' === method && cache[path]) {
      const cacheData = cache[path];

      // Unsetting the cache key ensures that the data is only used a single time.
      delete cache[path];
      return prepareResponse(cacheData, !!parse);
    } else if ('OPTIONS' === method && cache[method] && cache[method][path]) {
      const cacheData = cache[method][path];

      // Unsetting the cache key ensures that the data is only used a single time.
      delete cache[method][path];
      return prepareResponse(cacheData, !!parse);
    }
    return next(options);
  };
}

/**
 * This is a helper function that sends a success response.
 *
 * @param {Record<string, any>} responseData
 * @param {boolean}             parse
 * @return {Promise<any>} Promise with the response.
 */
function prepareResponse(responseData, parse) {
  return Promise.resolve(parse ? responseData.body : new window.Response(JSON.stringify(responseData.body), {
    status: 200,
    statusText: 'OK',
    headers: responseData.headers
  }));
}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (createPreloadingMiddleware);
//# sourceMappingURL=preloading.js.map

/***/ },

/***/ "./node_modules/@wordpress/api-fetch/build-module/middlewares/root-url.js"
/*!********************************************************************************!*\
  !*** ./node_modules/@wordpress/api-fetch/build-module/middlewares/root-url.js ***!
  \********************************************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _namespace_endpoint__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./namespace-endpoint */ "./node_modules/@wordpress/api-fetch/build-module/middlewares/namespace-endpoint.js");
/**
 * Internal dependencies
 */


/**
 * @param {string} rootURL
 * @return {import('../types').APIFetchMiddleware} Root URL middleware.
 */
const createRootURLMiddleware = rootURL => (options, next) => {
  return (0,_namespace_endpoint__WEBPACK_IMPORTED_MODULE_0__["default"])(options, optionsWithPath => {
    let url = optionsWithPath.url;
    let path = optionsWithPath.path;
    let apiRoot;
    if (typeof path === 'string') {
      apiRoot = rootURL;
      if (-1 !== rootURL.indexOf('?')) {
        path = path.replace('?', '&');
      }
      path = path.replace(/^\//, '');

      // API root may already include query parameter prefix if site is
      // configured to use plain permalinks.
      if ('string' === typeof apiRoot && -1 !== apiRoot.indexOf('?')) {
        path = path.replace('?', '&');
      }
      url = apiRoot + path;
    }
    return next({
      ...optionsWithPath,
      url
    });
  });
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (createRootURLMiddleware);
//# sourceMappingURL=root-url.js.map

/***/ },

/***/ "./node_modules/@wordpress/api-fetch/build-module/middlewares/theme-preview.js"
/*!*************************************************************************************!*\
  !*** ./node_modules/@wordpress/api-fetch/build-module/middlewares/theme-preview.js ***!
  \*************************************************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _wordpress_url__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/url */ "@wordpress/url");
/* harmony import */ var _wordpress_url__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_url__WEBPACK_IMPORTED_MODULE_0__);
/**
 * WordPress dependencies
 */


/**
 * This appends a `wp_theme_preview` parameter to the REST API request URL if
 * the admin URL contains a `theme` GET parameter.
 *
 * If the REST API request URL has contained the `wp_theme_preview` parameter as `''`,
 * then bypass this middleware.
 *
 * @param {Record<string, any>} themePath
 * @return {import('../types').APIFetchMiddleware} Preloading middleware.
 */
const createThemePreviewMiddleware = themePath => (options, next) => {
  if (typeof options.url === 'string') {
    const wpThemePreview = (0,_wordpress_url__WEBPACK_IMPORTED_MODULE_0__.getQueryArg)(options.url, 'wp_theme_preview');
    if (wpThemePreview === undefined) {
      options.url = (0,_wordpress_url__WEBPACK_IMPORTED_MODULE_0__.addQueryArgs)(options.url, {
        wp_theme_preview: themePath
      });
    } else if (wpThemePreview === '') {
      options.url = (0,_wordpress_url__WEBPACK_IMPORTED_MODULE_0__.removeQueryArgs)(options.url, 'wp_theme_preview');
    }
  }
  if (typeof options.path === 'string') {
    const wpThemePreview = (0,_wordpress_url__WEBPACK_IMPORTED_MODULE_0__.getQueryArg)(options.path, 'wp_theme_preview');
    if (wpThemePreview === undefined) {
      options.path = (0,_wordpress_url__WEBPACK_IMPORTED_MODULE_0__.addQueryArgs)(options.path, {
        wp_theme_preview: themePath
      });
    } else if (wpThemePreview === '') {
      options.path = (0,_wordpress_url__WEBPACK_IMPORTED_MODULE_0__.removeQueryArgs)(options.path, 'wp_theme_preview');
    }
  }
  return next(options);
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (createThemePreviewMiddleware);
//# sourceMappingURL=theme-preview.js.map

/***/ },

/***/ "./node_modules/@wordpress/api-fetch/build-module/middlewares/user-locale.js"
/*!***********************************************************************************!*\
  !*** ./node_modules/@wordpress/api-fetch/build-module/middlewares/user-locale.js ***!
  \***********************************************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _wordpress_url__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/url */ "@wordpress/url");
/* harmony import */ var _wordpress_url__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_url__WEBPACK_IMPORTED_MODULE_0__);
/**
 * WordPress dependencies
 */


/**
 * @type {import('../types').APIFetchMiddleware}
 */
const userLocaleMiddleware = (options, next) => {
  if (typeof options.url === 'string' && !(0,_wordpress_url__WEBPACK_IMPORTED_MODULE_0__.hasQueryArg)(options.url, '_locale')) {
    options.url = (0,_wordpress_url__WEBPACK_IMPORTED_MODULE_0__.addQueryArgs)(options.url, {
      _locale: 'user'
    });
  }
  if (typeof options.path === 'string' && !(0,_wordpress_url__WEBPACK_IMPORTED_MODULE_0__.hasQueryArg)(options.path, '_locale')) {
    options.path = (0,_wordpress_url__WEBPACK_IMPORTED_MODULE_0__.addQueryArgs)(options.path, {
      _locale: 'user'
    });
  }
  return next(options);
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (userLocaleMiddleware);
//# sourceMappingURL=user-locale.js.map

/***/ },

/***/ "./node_modules/@wordpress/api-fetch/build-module/utils/response.js"
/*!**************************************************************************!*\
  !*** ./node_modules/@wordpress/api-fetch/build-module/utils/response.js ***!
  \**************************************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   parseAndThrowError: () => (/* binding */ parseAndThrowError),
/* harmony export */   parseResponseAndNormalizeError: () => (/* binding */ parseResponseAndNormalizeError)
/* harmony export */ });
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__);
/**
 * WordPress dependencies
 */


/**
 * Parses the apiFetch response.
 *
 * @param {Response} response
 * @param {boolean}  shouldParseResponse
 *
 * @return {Promise<any> | null | Response} Parsed response.
 */
const parseResponse = (response, shouldParseResponse = true) => {
  if (shouldParseResponse) {
    if (response.status === 204) {
      return null;
    }
    return response.json ? response.json() : Promise.reject(response);
  }
  return response;
};

/**
 * Calls the `json` function on the Response, throwing an error if the response
 * doesn't have a json function or if parsing the json itself fails.
 *
 * @param {Response} response
 * @return {Promise<any>} Parsed response.
 */
const parseJsonAndNormalizeError = response => {
  const invalidJsonError = {
    code: 'invalid_json',
    message: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('The response is not a valid JSON response.')
  };
  if (!response || !response.json) {
    throw invalidJsonError;
  }
  return response.json().catch(() => {
    throw invalidJsonError;
  });
};

/**
 * Parses the apiFetch response properly and normalize response errors.
 *
 * @param {Response} response
 * @param {boolean}  shouldParseResponse
 *
 * @return {Promise<any>} Parsed response.
 */
const parseResponseAndNormalizeError = (response, shouldParseResponse = true) => {
  return Promise.resolve(parseResponse(response, shouldParseResponse)).catch(res => parseAndThrowError(res, shouldParseResponse));
};

/**
 * Parses a response, throwing an error if parsing the response fails.
 *
 * @param {Response} response
 * @param {boolean}  shouldParseResponse
 * @return {Promise<any>} Parsed response.
 */
function parseAndThrowError(response, shouldParseResponse = true) {
  if (!shouldParseResponse) {
    throw response;
  }
  return parseJsonAndNormalizeError(response).then(error => {
    const unknownError = {
      code: 'unknown_error',
      message: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('An unknown error occurred.')
    };
    throw error || unknownError;
  });
}
//# sourceMappingURL=response.js.map

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

/***/ "@wordpress/i18n"
/*!**************************!*\
  !*** external "wp.i18n" ***!
  \**************************/
(module) {

module.exports = wp.i18n;

/***/ },

/***/ "@wordpress/url"
/*!*************************!*\
  !*** external "wp.url" ***!
  \*************************/
(module) {

module.exports = wp.url;

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
/*!***************************************!*\
  !*** ./assets/jsx/settings-debug.jsx ***!
  \***************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/api-fetch */ "./node_modules/@wordpress/api-fetch/build-module/index.js");
/* harmony import */ var react_dom_client__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! react-dom/client */ "./node_modules/react-dom/client.js");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _regeneratorRuntime() { "use strict"; /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime = function _regeneratorRuntime() { return e; }; var t, e = {}, r = Object.prototype, n = r.hasOwnProperty, o = Object.defineProperty || function (t, e, r) { t[e] = r.value; }, i = "function" == typeof Symbol ? Symbol : {}, a = i.iterator || "@@iterator", c = i.asyncIterator || "@@asyncIterator", u = i.toStringTag || "@@toStringTag"; function define(t, e, r) { return Object.defineProperty(t, e, { value: r, enumerable: !0, configurable: !0, writable: !0 }), t[e]; } try { define({}, ""); } catch (t) { define = function define(t, e, r) { return t[e] = r; }; } function wrap(t, e, r, n) { var i = e && e.prototype instanceof Generator ? e : Generator, a = Object.create(i.prototype), c = new Context(n || []); return o(a, "_invoke", { value: makeInvokeMethod(t, r, c) }), a; } function tryCatch(t, e, r) { try { return { type: "normal", arg: t.call(e, r) }; } catch (t) { return { type: "throw", arg: t }; } } e.wrap = wrap; var h = "suspendedStart", l = "suspendedYield", f = "executing", s = "completed", y = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var p = {}; define(p, a, function () { return this; }); var d = Object.getPrototypeOf, v = d && d(d(values([]))); v && v !== r && n.call(v, a) && (p = v); var g = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(p); function defineIteratorMethods(t) { ["next", "throw", "return"].forEach(function (e) { define(t, e, function (t) { return this._invoke(e, t); }); }); } function AsyncIterator(t, e) { function invoke(r, o, i, a) { var c = tryCatch(t[r], t, o); if ("throw" !== c.type) { var u = c.arg, h = u.value; return h && "object" == _typeof(h) && n.call(h, "__await") ? e.resolve(h.__await).then(function (t) { invoke("next", t, i, a); }, function (t) { invoke("throw", t, i, a); }) : e.resolve(h).then(function (t) { u.value = t, i(u); }, function (t) { return invoke("throw", t, i, a); }); } a(c.arg); } var r; o(this, "_invoke", { value: function value(t, n) { function callInvokeWithMethodAndArg() { return new e(function (e, r) { invoke(t, n, e, r); }); } return r = r ? r.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); } }); } function makeInvokeMethod(e, r, n) { var o = h; return function (i, a) { if (o === f) throw Error("Generator is already running"); if (o === s) { if ("throw" === i) throw a; return { value: t, done: !0 }; } for (n.method = i, n.arg = a;;) { var c = n.delegate; if (c) { var u = maybeInvokeDelegate(c, n); if (u) { if (u === y) continue; return u; } } if ("next" === n.method) n.sent = n._sent = n.arg;else if ("throw" === n.method) { if (o === h) throw o = s, n.arg; n.dispatchException(n.arg); } else "return" === n.method && n.abrupt("return", n.arg); o = f; var p = tryCatch(e, r, n); if ("normal" === p.type) { if (o = n.done ? s : l, p.arg === y) continue; return { value: p.arg, done: n.done }; } "throw" === p.type && (o = s, n.method = "throw", n.arg = p.arg); } }; } function maybeInvokeDelegate(e, r) { var n = r.method, o = e.iterator[n]; if (o === t) return r.delegate = null, "throw" === n && e.iterator.return && (r.method = "return", r.arg = t, maybeInvokeDelegate(e, r), "throw" === r.method) || "return" !== n && (r.method = "throw", r.arg = new TypeError("The iterator does not provide a '" + n + "' method")), y; var i = tryCatch(o, e.iterator, r.arg); if ("throw" === i.type) return r.method = "throw", r.arg = i.arg, r.delegate = null, y; var a = i.arg; return a ? a.done ? (r[e.resultName] = a.value, r.next = e.nextLoc, "return" !== r.method && (r.method = "next", r.arg = t), r.delegate = null, y) : a : (r.method = "throw", r.arg = new TypeError("iterator result is not an object"), r.delegate = null, y); } function pushTryEntry(t) { var e = { tryLoc: t[0] }; 1 in t && (e.catchLoc = t[1]), 2 in t && (e.finallyLoc = t[2], e.afterLoc = t[3]), this.tryEntries.push(e); } function resetTryEntry(t) { var e = t.completion || {}; e.type = "normal", delete e.arg, t.completion = e; } function Context(t) { this.tryEntries = [{ tryLoc: "root" }], t.forEach(pushTryEntry, this), this.reset(!0); } function values(e) { if (e || "" === e) { var r = e[a]; if (r) return r.call(e); if ("function" == typeof e.next) return e; if (!isNaN(e.length)) { var o = -1, i = function next() { for (; ++o < e.length;) if (n.call(e, o)) return next.value = e[o], next.done = !1, next; return next.value = t, next.done = !0, next; }; return i.next = i; } } throw new TypeError(_typeof(e) + " is not iterable"); } return GeneratorFunction.prototype = GeneratorFunctionPrototype, o(g, "constructor", { value: GeneratorFunctionPrototype, configurable: !0 }), o(GeneratorFunctionPrototype, "constructor", { value: GeneratorFunction, configurable: !0 }), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, u, "GeneratorFunction"), e.isGeneratorFunction = function (t) { var e = "function" == typeof t && t.constructor; return !!e && (e === GeneratorFunction || "GeneratorFunction" === (e.displayName || e.name)); }, e.mark = function (t) { return Object.setPrototypeOf ? Object.setPrototypeOf(t, GeneratorFunctionPrototype) : (t.__proto__ = GeneratorFunctionPrototype, define(t, u, "GeneratorFunction")), t.prototype = Object.create(g), t; }, e.awrap = function (t) { return { __await: t }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, c, function () { return this; }), e.AsyncIterator = AsyncIterator, e.async = function (t, r, n, o, i) { void 0 === i && (i = Promise); var a = new AsyncIterator(wrap(t, r, n, o), i); return e.isGeneratorFunction(r) ? a : a.next().then(function (t) { return t.done ? t.value : a.next(); }); }, defineIteratorMethods(g), define(g, u, "Generator"), define(g, a, function () { return this; }), define(g, "toString", function () { return "[object Generator]"; }), e.keys = function (t) { var e = Object(t), r = []; for (var n in e) r.push(n); return r.reverse(), function next() { for (; r.length;) { var t = r.pop(); if (t in e) return next.value = t, next.done = !1, next; } return next.done = !0, next; }; }, e.values = values, Context.prototype = { constructor: Context, reset: function reset(e) { if (this.prev = 0, this.next = 0, this.sent = this._sent = t, this.done = !1, this.delegate = null, this.method = "next", this.arg = t, this.tryEntries.forEach(resetTryEntry), !e) for (var r in this) "t" === r.charAt(0) && n.call(this, r) && !isNaN(+r.slice(1)) && (this[r] = t); }, stop: function stop() { this.done = !0; var t = this.tryEntries[0].completion; if ("throw" === t.type) throw t.arg; return this.rval; }, dispatchException: function dispatchException(e) { if (this.done) throw e; var r = this; function handle(n, o) { return a.type = "throw", a.arg = e, r.next = n, o && (r.method = "next", r.arg = t), !!o; } for (var o = this.tryEntries.length - 1; o >= 0; --o) { var i = this.tryEntries[o], a = i.completion; if ("root" === i.tryLoc) return handle("end"); if (i.tryLoc <= this.prev) { var c = n.call(i, "catchLoc"), u = n.call(i, "finallyLoc"); if (c && u) { if (this.prev < i.catchLoc) return handle(i.catchLoc, !0); if (this.prev < i.finallyLoc) return handle(i.finallyLoc); } else if (c) { if (this.prev < i.catchLoc) return handle(i.catchLoc, !0); } else { if (!u) throw Error("try statement without catch or finally"); if (this.prev < i.finallyLoc) return handle(i.finallyLoc); } } } }, abrupt: function abrupt(t, e) { for (var r = this.tryEntries.length - 1; r >= 0; --r) { var o = this.tryEntries[r]; if (o.tryLoc <= this.prev && n.call(o, "finallyLoc") && this.prev < o.finallyLoc) { var i = o; break; } } i && ("break" === t || "continue" === t) && i.tryLoc <= e && e <= i.finallyLoc && (i = null); var a = i ? i.completion : {}; return a.type = t, a.arg = e, i ? (this.method = "next", this.next = i.finallyLoc, y) : this.complete(a); }, complete: function complete(t, e) { if ("throw" === t.type) throw t.arg; return "break" === t.type || "continue" === t.type ? this.next = t.arg : "return" === t.type ? (this.rval = this.arg = t.arg, this.method = "return", this.next = "end") : "normal" === t.type && e && (this.next = e), y; }, finish: function finish(t) { for (var e = this.tryEntries.length - 1; e >= 0; --e) { var r = this.tryEntries[e]; if (r.finallyLoc === t) return this.complete(r.completion, r.afterLoc), resetTryEntry(r), y; } }, catch: function _catch(t) { for (var e = this.tryEntries.length - 1; e >= 0; --e) { var r = this.tryEntries[e]; if (r.tryLoc === t) { var n = r.completion; if ("throw" === n.type) { var o = n.arg; resetTryEntry(r); } return o; } } throw Error("illegal catch attempt"); }, delegateYield: function delegateYield(e, r, n) { return this.delegate = { iterator: values(e), resultName: r, nextLoc: n }, "next" === this.method && (this.arg = t), y; } }, e; }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t.return && (u = t.return(), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }



var config = window.publishpressFutureSettingsDebug || {};
var text = config.text || {};
function DebugLogAutoRefresh() {
  var _useState = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(false),
    _useState2 = _slicedToArray(_useState, 2),
    autoRefresh = _useState2[0],
    setAutoRefresh = _useState2[1];
  var _useState3 = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(10),
    _useState4 = _slicedToArray(_useState3, 2),
    refreshInterval = _useState4[0],
    setRefreshInterval = _useState4[1];
  var _useState5 = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(false),
    _useState6 = _slicedToArray(_useState5, 2),
    isRefreshing = _useState6[0],
    setIsRefreshing = _useState6[1];
  var _useState7 = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(''),
    _useState8 = _slicedToArray(_useState7, 2),
    error = _useState8[0],
    setError = _useState8[1];
  var _useState9 = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(null),
    _useState10 = _slicedToArray(_useState9, 2),
    lastRefreshedSeconds = _useState10[0],
    setLastRefreshedSeconds = _useState10[1];
  var intervalRef = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useRef)(null);
  var lastRefreshTimeRef = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useRef)(null);
  var tickRef = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useRef)(null);
  var fetchLogs = /*#__PURE__*/function () {
    var _ref = _asyncToGenerator( /*#__PURE__*/_regeneratorRuntime().mark(function _callee() {
      var _document$getElementB, _document$querySelect;
      var logCount, groupByChecked, groupByRequest, triggerOnly, apiRoot, data, textarea;
      return _regeneratorRuntime().wrap(function _callee$(_context) {
        while (1) switch (_context.prev = _context.next) {
          case 0:
            logCount = ((_document$getElementB = document.getElementById('log-count')) === null || _document$getElementB === void 0 ? void 0 : _document$getElementB.value) || 500;
            groupByChecked = document.querySelector('input[name="group_by_request"]:checked');
            groupByRequest = groupByChecked ? groupByChecked.value : 1;
            triggerOnly = (_document$querySelect = document.querySelector('input[name="trigger_activated_only"]')) !== null && _document$querySelect !== void 0 && _document$querySelect.checked ? 1 : 0;
            setIsRefreshing(true);
            setError('');
            _context.prev = 6;
            apiRoot = config.apiRoot || '';
            _context.next = 10;
            return (0,_wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_1__["default"])({
              url: "".concat(apiRoot, "?log_count=").concat(logCount, "&group_by_request=").concat(groupByRequest, "&trigger_activated_only=").concat(triggerOnly),
              headers: {
                'X-WP-Nonce': config.nonce || ''
              }
            });
          case 10:
            data = _context.sent;
            textarea = document.querySelector('.pp-debug-log textarea');
            if (textarea) {
              if (data.log_text) {
                textarea.value = data.log_text;
              } else if (data.total_logs_unfiltered === 0) {
                textarea.value = text.emptyLog || 'Debugging table is currently empty.';
              } else {
                textarea.value = text.noResults || 'No results match the current filter.';
              }
              textarea.scrollTop = textarea.scrollHeight;
            }
            lastRefreshTimeRef.current = Date.now();
            setLastRefreshedSeconds(0);
            _context.next = 20;
            break;
          case 17:
            _context.prev = 17;
            _context.t0 = _context["catch"](6);
            setError(text.refreshError || 'Failed to refresh log data.');
          case 20:
            _context.prev = 20;
            setIsRefreshing(false);
            return _context.finish(20);
          case 23:
          case "end":
            return _context.stop();
        }
      }, _callee, null, [[6, 17, 20, 23]]);
    }));
    return function fetchLogs() {
      return _ref.apply(this, arguments);
    };
  }();
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(function () {
    if (autoRefresh) {
      fetchLogs();
      intervalRef.current = window.setInterval(fetchLogs, refreshInterval * 1000);
      tickRef.current = window.setInterval(function () {
        if (lastRefreshTimeRef.current !== null) {
          setLastRefreshedSeconds(Math.floor((Date.now() - lastRefreshTimeRef.current) / 1000));
        }
      }, 1000);
    } else {
      window.clearInterval(intervalRef.current);
      window.clearInterval(tickRef.current);
      setLastRefreshedSeconds(null);
    }
    return function () {
      window.clearInterval(intervalRef.current);
      window.clearInterval(tickRef.current);
    };
  }, [autoRefresh, refreshInterval]);
  return /*#__PURE__*/React.createElement("div", {
    className: "pp-debug-log-option pp-debug-log-autorefresh"
  }, /*#__PURE__*/React.createElement("label", {
    className: "pp-checkbox-label"
  }, /*#__PURE__*/React.createElement("input", {
    type: "checkbox",
    checked: autoRefresh,
    onChange: function onChange(e) {
      return setAutoRefresh(e.target.checked);
    }
  }), ' ', text.autoRefresh || 'Auto-refresh'), autoRefresh && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("label", {
    htmlFor: "pp-autorefresh-interval",
    style: {
      marginLeft: '10px'
    }
  }, text.refreshInterval || 'Refresh interval:'), /*#__PURE__*/React.createElement("select", {
    id: "pp-autorefresh-interval",
    value: refreshInterval,
    onChange: function onChange(e) {
      return setRefreshInterval(Number(e.target.value));
    },
    style: {
      marginLeft: '5px'
    }
  }, /*#__PURE__*/React.createElement("option", {
    value: 1
  }, "1 ", text.seconds || 'seconds'), /*#__PURE__*/React.createElement("option", {
    value: 3
  }, "3 ", text.seconds || 'seconds'), /*#__PURE__*/React.createElement("option", {
    value: 5
  }, "5 ", text.seconds || 'seconds'), /*#__PURE__*/React.createElement("option", {
    value: 10
  }, "10 ", text.seconds || 'seconds'), /*#__PURE__*/React.createElement("option", {
    value: 30
  }, "30 ", text.seconds || 'seconds'), /*#__PURE__*/React.createElement("option", {
    value: 60
  }, "60 ", text.seconds || 'seconds')), isRefreshing && /*#__PURE__*/React.createElement("span", {
    style: {
      marginLeft: '10px',
      fontStyle: 'italic'
    }
  }, text.refreshing || 'Refreshing...'), !isRefreshing && lastRefreshedSeconds !== null && /*#__PURE__*/React.createElement("span", {
    style: {
      marginLeft: '10px',
      color: '#666'
    }
  }, text.lastRefreshed || 'Last refreshed:', " ", lastRefreshedSeconds, " ", text.secondsAgo || 'seconds ago'), error && /*#__PURE__*/React.createElement("span", {
    style: {
      marginLeft: '10px',
      color: 'red'
    }
  }, error)));
}
var container = document.getElementById('pp-debug-log-autorefresh');
if (container) {
  var root = (0,react_dom_client__WEBPACK_IMPORTED_MODULE_2__.createRoot)(container);
  root.render( /*#__PURE__*/React.createElement(DebugLogAutoRefresh, null));
}
})();

/******/ })()
;
//# sourceMappingURL=settingsDebug.js.map