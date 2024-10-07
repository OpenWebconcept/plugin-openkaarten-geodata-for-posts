/*
 * ATTENTION: An "eval-source-map" devtool has been used.
 * This devtool is neither made for production nor for readable output files.
 * It uses "eval()" calls to create a separate source file with attached SourceMaps in the browser devtools.
 * If you are trying to read the output file, select a different devtool (https://webpack.js.org/configuration/devtool/)
 * or disable the default devtool with "devtool: false".
 * If you are looking for production-ready output files, see mode: "production" (https://webpack.js.org/configuration/mode/).
 */
/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./src/scripts/openstreetmap.js":
/*!**************************************!*\
  !*** ./src/scripts/openstreetmap.js ***!
  \**************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var leaflet__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! leaflet */ \"./node_modules/leaflet/dist/leaflet-src.js\");\n/* harmony import */ var leaflet__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(leaflet__WEBPACK_IMPORTED_MODULE_0__);\n\n\n// Retrieve the locations and map configuration from the global window object.\nvar _window$leaflet_vars = window.leaflet_vars,\n  centerLat = _window$leaflet_vars.centerLat,\n  centerLong = _window$leaflet_vars.centerLong,\n  defaultZoom = _window$leaflet_vars.defaultZoom,\n  setMarker = _window$leaflet_vars.setMarker;\n\n// Set the map configuration.\nvar config = {\n  \"centerX\": centerLong,\n  \"centerY\": centerLat,\n  \"minimumZoom\": 4,\n  \"maximumZoom\": 16,\n  \"defaultZoom\": defaultZoom,\n  \"enableZoomControl\": true,\n  \"enableBoxZoomControl\": true\n};\n\n// Create the map with the specified configuration.\nwindow.onload = function () {\n  var map = new (leaflet__WEBPACK_IMPORTED_MODULE_0___default().Map)('map', {\n    center: [config.centerY, config.centerX],\n    zoom: config.defaultZoom,\n    minZoom: config.minimumZoom,\n    maxZoom: config.maximumZoom,\n    boxZoom: config.enableBoxZoomControl\n  });\n\n  // Add the OpenStreetMap tile layer to the map.\n  leaflet__WEBPACK_IMPORTED_MODULE_0___default().tileLayer('https://{s}.tile.osm.org/{z}/{x}/{y}.png', {\n    attribution: '&copy; <a href=\"https://osm.org/copyright\">OpenStreetMap</a> contributors'\n  }).addTo(map);\n\n  // Add a marker to the map if the location is set.\n  if (setMarker) {\n    addMarker(map, config.centerY, config.centerX);\n  }\n  map.on('click', function (e) {\n    var coord = e.latlng;\n    var lat = coord.lat;\n    var lng = coord.lng;\n\n    // Add a draggable marker to the map.\n    addMarker(map, lat, lng);\n    map.panTo(new (leaflet__WEBPACK_IMPORTED_MODULE_0___default().LatLng)(lat, lng));\n\n    // Update the form fields with the new coordinates.\n    updateGeoFields(lat, lng);\n  });\n};\nfunction addMarker(map, lat, lng) {\n  // Remove all existing markers from the map.\n  map.eachLayer(function (layer) {\n    if (layer instanceof (leaflet__WEBPACK_IMPORTED_MODULE_0___default().Marker)) {\n      map.removeLayer(layer);\n    }\n  });\n\n  // Create a custom marker icon with the location color and icon.\n  var customIconHtml = \"<div style='background-color:\" + location.color + \";' class='marker-pin'></div>\";\n  if (location.icon) {\n    customIconHtml += \"<span class='marker-icon'><img src='\" + location.icon + \"'  alt='marker icon' /></span>\";\n  }\n  var customIcon = leaflet__WEBPACK_IMPORTED_MODULE_0___default().divIcon({\n    className: 'leaflet-custom-icon',\n    html: customIconHtml,\n    iconSize: [30, 42],\n    iconAnchor: [15, 42]\n  });\n  var iconOptions = {\n    icon: customIcon,\n    draggable: 'true'\n  };\n  var marker = leaflet__WEBPACK_IMPORTED_MODULE_0___default().marker([lat, lng], iconOptions);\n  marker.on('dragend', function (event) {\n    var marker = event.target;\n    var position = marker.getLatLng();\n    marker.setLatLng(new (leaflet__WEBPACK_IMPORTED_MODULE_0___default().LatLng)(position.lat, position.lng), {\n      draggable: 'true'\n    });\n    map.panTo(new (leaflet__WEBPACK_IMPORTED_MODULE_0___default().LatLng)(position.lat, position.lng));\n\n    // Update the form fields with the new coordinates.\n    updateGeoFields(position.lat, position.lng);\n  });\n  map.addLayer(marker);\n}\nfunction updateGeoFields(lat, lng) {\n  if (document.getElementById('field_geo_latitude') !== null) {\n    document.getElementById('field_geo_latitude').value = lat;\n  }\n  if (document.getElementById('field_geo_longitude') !== null) {\n    document.getElementById('field_geo_longitude').value = lng;\n  }\n}//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,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RUFDQSxJQUFLNEIsUUFBUSxDQUFDQyxjQUFjLENBQUMscUJBQXFCLENBQUMsS0FBSyxJQUFJLEVBQUc7SUFDN0RELFFBQVEsQ0FBQ0MsY0FBYyxDQUFFLHFCQUFzQixDQUFDLENBQUNDLEtBQUssR0FBRzdCLEdBQUc7RUFDOUQ7QUFDRiIsInNvdXJjZXMiOlsid2VicGFjazovL29wZW5rYWFydGVuLWdlb2RhdGEvLi9zcmMvc2NyaXB0cy9vcGVuc3RyZWV0bWFwLmpzPzM0NjgiXSwic291cmNlc0NvbnRlbnQiOlsiaW1wb3J0IEwgZnJvbSBcImxlYWZsZXRcIjtcblxuLy8gUmV0cmlldmUgdGhlIGxvY2F0aW9ucyBhbmQgbWFwIGNvbmZpZ3VyYXRpb24gZnJvbSB0aGUgZ2xvYmFsIHdpbmRvdyBvYmplY3QuXG5jb25zdCB7Y2VudGVyTGF0LCBjZW50ZXJMb25nLCBkZWZhdWx0Wm9vbSwgc2V0TWFya2VyIH0gPSB3aW5kb3cubGVhZmxldF92YXJzO1xuXG4vLyBTZXQgdGhlIG1hcCBjb25maWd1cmF0aW9uLlxuY29uc3QgY29uZmlnID0ge1xuICBcImNlbnRlclhcIjogY2VudGVyTG9uZyxcbiAgXCJjZW50ZXJZXCI6IGNlbnRlckxhdCxcbiAgXCJtaW5pbXVtWm9vbVwiOiA0LFxuICBcIm1heGltdW1ab29tXCI6IDE2LFxuICBcImRlZmF1bHRab29tXCI6IGRlZmF1bHRab29tLFxuICBcImVuYWJsZVpvb21Db250cm9sXCI6IHRydWUsXG4gIFwiZW5hYmxlQm94Wm9vbUNvbnRyb2xcIjogdHJ1ZVxufVxuXG4vLyBDcmVhdGUgdGhlIG1hcCB3aXRoIHRoZSBzcGVjaWZpZWQgY29uZmlndXJhdGlvbi5cbndpbmRvdy5vbmxvYWQgPSBmdW5jdGlvbigpIHtcbiAgY29uc3QgbWFwID0gbmV3IEwuTWFwKCdtYXAnLCB7XG4gICAgY2VudGVyOiBbY29uZmlnLmNlbnRlclksIGNvbmZpZy5jZW50ZXJYXSxcbiAgICB6b29tOiBjb25maWcuZGVmYXVsdFpvb20sXG4gICAgbWluWm9vbTogY29uZmlnLm1pbmltdW1ab29tLFxuICAgIG1heFpvb206IGNvbmZpZy5tYXhpbXVtWm9vbSxcbiAgICBib3hab29tOiBjb25maWcuZW5hYmxlQm94Wm9vbUNvbnRyb2xcbiAgfSk7XG5cbiAgLy8gQWRkIHRoZSBPcGVuU3RyZWV0TWFwIHRpbGUgbGF5ZXIgdG8gdGhlIG1hcC5cbiAgTC50aWxlTGF5ZXIoJ2h0dHBzOi8ve3N9LnRpbGUub3NtLm9yZy97en0ve3h9L3t5fS5wbmcnLCB7XG4gICAgYXR0cmlidXRpb246ICcmY29weTsgPGEgaHJlZj1cImh0dHBzOi8vb3NtLm9yZy9jb3B5cmlnaHRcIj5PcGVuU3RyZWV0TWFwPC9hPiBjb250cmlidXRvcnMnXG4gIH0pLmFkZFRvKG1hcCk7XG5cbiAgLy8gQWRkIGEgbWFya2VyIHRvIHRoZSBtYXAgaWYgdGhlIGxvY2F0aW9uIGlzIHNldC5cbiAgaWYgKHNldE1hcmtlcikge1xuICAgIGFkZE1hcmtlciggbWFwLCBjb25maWcuY2VudGVyWSwgY29uZmlnLmNlbnRlclggKTtcbiAgfVxuXG4gIG1hcC5vbiggJ2NsaWNrJywgZnVuY3Rpb24gKGUpIHtcbiAgICB2YXIgY29vcmQgPSBlLmxhdGxuZztcbiAgICB2YXIgbGF0ID0gY29vcmQubGF0O1xuICAgIHZhciBsbmcgPSBjb29yZC5sbmc7XG5cbiAgICAvLyBBZGQgYSBkcmFnZ2FibGUgbWFya2VyIHRvIHRoZSBtYXAuXG4gICAgYWRkTWFya2VyKCBtYXAsIGxhdCwgbG5nICk7XG4gICAgbWFwLnBhblRvKCBuZXcgTC5MYXRMbmcoIGxhdCwgbG5nICkgKTtcblxuICAgIC8vIFVwZGF0ZSB0aGUgZm9ybSBmaWVsZHMgd2l0aCB0aGUgbmV3IGNvb3JkaW5hdGVzLlxuICAgIHVwZGF0ZUdlb0ZpZWxkcyggbGF0LCBsbmcgKTtcbiAgfSApO1xufTtcblxuZnVuY3Rpb24gYWRkTWFya2VyKCBtYXAsIGxhdCwgbG5nICkge1xuICAvLyBSZW1vdmUgYWxsIGV4aXN0aW5nIG1hcmtlcnMgZnJvbSB0aGUgbWFwLlxuICBtYXAuZWFjaExheWVyKCBmdW5jdGlvbiAobGF5ZXIpIHtcbiAgICBpZiAobGF5ZXIgaW5zdGFuY2VvZiBMLk1hcmtlcikge1xuICAgICAgbWFwLnJlbW92ZUxheWVyKCBsYXllciApO1xuICAgIH1cbiAgfSApO1xuXG4gIC8vIENyZWF0ZSBhIGN1c3RvbSBtYXJrZXIgaWNvbiB3aXRoIHRoZSBsb2NhdGlvbiBjb2xvciBhbmQgaWNvbi5cbiAgbGV0IGN1c3RvbUljb25IdG1sID0gXCI8ZGl2IHN0eWxlPSdiYWNrZ3JvdW5kLWNvbG9yOlwiICsgbG9jYXRpb24uY29sb3IgKyBcIjsnIGNsYXNzPSdtYXJrZXItcGluJz48L2Rpdj5cIjtcbiAgaWYgKGxvY2F0aW9uLmljb24pIHtcbiAgICBjdXN0b21JY29uSHRtbCArPSBcIjxzcGFuIGNsYXNzPSdtYXJrZXItaWNvbic+PGltZyBzcmM9J1wiICsgbG9jYXRpb24uaWNvbiArIFwiJyAgYWx0PSdtYXJrZXIgaWNvbicgLz48L3NwYW4+XCI7XG4gIH1cblxuICB2YXIgY3VzdG9tSWNvbiA9IEwuZGl2SWNvbigge1xuICAgIGNsYXNzTmFtZTogJ2xlYWZsZXQtY3VzdG9tLWljb24nLFxuICAgIGh0bWw6IGN1c3RvbUljb25IdG1sLFxuICAgIGljb25TaXplOiBbMzAsIDQyXSxcbiAgICBpY29uQW5jaG9yOiBbMTUsIDQyXVxuICB9ICk7XG5cbiAgbGV0IGljb25PcHRpb25zID0ge1xuICAgIGljb246IGN1c3RvbUljb24sXG4gICAgZHJhZ2dhYmxlOiAndHJ1ZSdcbiAgfVxuXG4gIHZhciBtYXJrZXIgPSBMLm1hcmtlciggW2xhdCwgbG5nXSwgaWNvbk9wdGlvbnMgKTtcbiAgbWFya2VyLm9uKCAnZHJhZ2VuZCcsIGZ1bmN0aW9uIChldmVudCkge1xuICAgIHZhciBtYXJrZXIgPSBldmVudC50YXJnZXQ7XG4gICAgdmFyIHBvc2l0aW9uID0gbWFya2VyLmdldExhdExuZygpO1xuICAgIG1hcmtlci5zZXRMYXRMbmcoIG5ldyBMLkxhdExuZyggcG9zaXRpb24ubGF0LCBwb3NpdGlvbi5sbmcgKSwge2RyYWdnYWJsZTogJ3RydWUnfSApO1xuICAgIG1hcC5wYW5UbyggbmV3IEwuTGF0TG5nKCBwb3NpdGlvbi5sYXQsIHBvc2l0aW9uLmxuZyApICk7XG5cbiAgICAvLyBVcGRhdGUgdGhlIGZvcm0gZmllbGRzIHdpdGggdGhlIG5ldyBjb29yZGluYXRlcy5cbiAgICB1cGRhdGVHZW9GaWVsZHMoIHBvc2l0aW9uLmxhdCwgcG9zaXRpb24ubG5nICk7XG4gIH0gKTtcbiAgbWFwLmFkZExheWVyKCBtYXJrZXIgKTtcbn1cblxuZnVuY3Rpb24gdXBkYXRlR2VvRmllbGRzKGxhdCwgbG5nKSB7XG4gIGlmICggZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ2ZpZWxkX2dlb19sYXRpdHVkZScpICE9PSBudWxsICkge1xuICAgIGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCAnZmllbGRfZ2VvX2xhdGl0dWRlJyApLnZhbHVlID0gbGF0O1xuICB9XG4gIGlmICggZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ2ZpZWxkX2dlb19sb25naXR1ZGUnKSAhPT0gbnVsbCApIHtcbiAgICBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCggJ2ZpZWxkX2dlb19sb25naXR1ZGUnICkudmFsdWUgPSBsbmc7XG4gIH1cbn1cbiJdLCJuYW1lcyI6WyJMIiwiX3dpbmRvdyRsZWFmbGV0X3ZhcnMiLCJ3aW5kb3ciLCJsZWFmbGV0X3ZhcnMiLCJjZW50ZXJMYXQiLCJjZW50ZXJMb25nIiwiZGVmYXVsdFpvb20iLCJzZXRNYXJrZXIiLCJjb25maWciLCJvbmxvYWQiLCJtYXAiLCJNYXAiLCJjZW50ZXIiLCJjZW50ZXJZIiwiY2VudGVyWCIsInpvb20iLCJtaW5ab29tIiwibWluaW11bVpvb20iLCJtYXhab29tIiwibWF4aW11bVpvb20iLCJib3hab29tIiwiZW5hYmxlQm94Wm9vbUNvbnRyb2wiLCJ0aWxlTGF5ZXIiLCJhdHRyaWJ1dGlvbiIsImFkZFRvIiwiYWRkTWFya2VyIiwib24iLCJlIiwiY29vcmQiLCJsYXRsbmciLCJsYXQiLCJsbmciLCJwYW5UbyIsIkxhdExuZyIsInVwZGF0ZUdlb0ZpZWxkcyIsImVhY2hMYXllciIsImxheWVyIiwiTWFya2VyIiwicmVtb3ZlTGF5ZXIiLCJjdXN0b21JY29uSHRtbCIsImxvY2F0aW9uIiwiY29sb3IiLCJpY29uIiwiY3VzdG9tSWNvbiIsImRpdkljb24iLCJjbGFzc05hbWUiLCJodG1sIiwiaWNvblNpemUiLCJpY29uQW5jaG9yIiwiaWNvbk9wdGlvbnMiLCJkcmFnZ2FibGUiLCJtYXJrZXIiLCJldmVudCIsInRhcmdldCIsInBvc2l0aW9uIiwiZ2V0TGF0TG5nIiwic2V0TGF0TG5nIiwiYWRkTGF5ZXIiLCJkb2N1bWVudCIsImdldEVsZW1lbnRCeUlkIiwidmFsdWUiXSwic291cmNlUm9vdCI6IiJ9\n//# sourceURL=webpack-internal:///./src/scripts/openstreetmap.js\n");

/***/ }),

/***/ "./node_modules/leaflet/dist/leaflet-src.js":
/*!**************************************************!*\
  !*** ./node_modules/leaflet/dist/leaflet-src.js ***!
  \**************************************************/
/***/ (function(__unused_webpack_module, exports) {


/***/ }),

/***/ "./src/styles/openstreetmap.scss":
/*!***************************************!*\
  !*** ./src/styles/openstreetmap.scss ***!
  \***************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n// extracted by mini-css-extract-plugin\n//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiLi9zcmMvc3R5bGVzL29wZW5zdHJlZXRtYXAuc2NzcyIsIm1hcHBpbmdzIjoiO0FBQUEiLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly9vcGVua2FhcnRlbi1nZW9kYXRhLy4vc3JjL3N0eWxlcy9vcGVuc3RyZWV0bWFwLnNjc3M/YmYyZSJdLCJzb3VyY2VzQ29udGVudCI6WyIvLyBleHRyYWN0ZWQgYnkgbWluaS1jc3MtZXh0cmFjdC1wbHVnaW5cbmV4cG9ydCB7fTsiXSwibmFtZXMiOltdLCJzb3VyY2VSb290IjoiIn0=\n//# sourceURL=webpack-internal:///./src/styles/openstreetmap.scss\n");

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
/******/ 		__webpack_modules__[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = __webpack_modules__;
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/chunk loaded */
/******/ 	(() => {
/******/ 		var deferred = [];
/******/ 		__webpack_require__.O = (result, chunkIds, fn, priority) => {
/******/ 			if(chunkIds) {
/******/ 				priority = priority || 0;
/******/ 				for(var i = deferred.length; i > 0 && deferred[i - 1][2] > priority; i--) deferred[i] = deferred[i - 1];
/******/ 				deferred[i] = [chunkIds, fn, priority];
/******/ 				return;
/******/ 			}
/******/ 			var notFulfilled = Infinity;
/******/ 			for (var i = 0; i < deferred.length; i++) {
/******/ 				var [chunkIds, fn, priority] = deferred[i];
/******/ 				var fulfilled = true;
/******/ 				for (var j = 0; j < chunkIds.length; j++) {
/******/ 					if ((priority & 1 === 0 || notFulfilled >= priority) && Object.keys(__webpack_require__.O).every((key) => (__webpack_require__.O[key](chunkIds[j])))) {
/******/ 						chunkIds.splice(j--, 1);
/******/ 					} else {
/******/ 						fulfilled = false;
/******/ 						if(priority < notFulfilled) notFulfilled = priority;
/******/ 					}
/******/ 				}
/******/ 				if(fulfilled) {
/******/ 					deferred.splice(i--, 1)
/******/ 					var r = fn();
/******/ 					if (r !== undefined) result = r;
/******/ 				}
/******/ 			}
/******/ 			return result;
/******/ 		};
/******/ 	})();
/******/ 	
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
/******/ 	/* webpack/runtime/jsonp chunk loading */
/******/ 	(() => {
/******/ 		// no baseURI
/******/ 		
/******/ 		// object to store loaded and loading chunks
/******/ 		// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 		// [resolve, reject, Promise] = chunk loading, 0 = chunk loaded
/******/ 		var installedChunks = {
/******/ 			"/scripts/openstreetmap": 0,
/******/ 			"styles/openstreetmap": 0
/******/ 		};
/******/ 		
/******/ 		// no chunk on demand loading
/******/ 		
/******/ 		// no prefetching
/******/ 		
/******/ 		// no preloaded
/******/ 		
/******/ 		// no HMR
/******/ 		
/******/ 		// no HMR manifest
/******/ 		
/******/ 		__webpack_require__.O.j = (chunkId) => (installedChunks[chunkId] === 0);
/******/ 		
/******/ 		// install a JSONP callback for chunk loading
/******/ 		var webpackJsonpCallback = (parentChunkLoadingFunction, data) => {
/******/ 			var [chunkIds, moreModules, runtime] = data;
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
/******/ 			return __webpack_require__.O(result);
/******/ 		}
/******/ 		
/******/ 		var chunkLoadingGlobal = self["webpackChunkopenkaarten_geodata"] = self["webpackChunkopenkaarten_geodata"] || [];
/******/ 		chunkLoadingGlobal.forEach(webpackJsonpCallback.bind(null, 0));
/******/ 		chunkLoadingGlobal.push = webpackJsonpCallback.bind(null, chunkLoadingGlobal.push.bind(chunkLoadingGlobal));
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module depends on other loaded chunks and execution need to be delayed
/******/ 	__webpack_require__.O(undefined, ["styles/openstreetmap"], () => (__webpack_require__("./src/scripts/openstreetmap.js")))
/******/ 	var __webpack_exports__ = __webpack_require__.O(undefined, ["styles/openstreetmap"], () => (__webpack_require__("./src/styles/openstreetmap.scss")))
/******/ 	__webpack_exports__ = __webpack_require__.O(__webpack_exports__);
/******/ 	
/******/ })()
;