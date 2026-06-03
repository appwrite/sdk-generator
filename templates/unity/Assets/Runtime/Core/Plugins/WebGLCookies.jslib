mergeInto(LibraryManager.library, {
   OpenUrlSamePage: function (urlPtr) {
    var url = UTF8ToString(urlPtr);
    window.location.href = url;
  },
  // Enable credentials on fetch/XHR so cookies are sent/received on cross-origin requests
  EnableWebGLHttpCredentials: function(enable) {
    try {
      if (enable) {
        // Patch fetch to default credentials: 'include'
        if (typeof window !== 'undefined' && window.fetch && !window.__aw_fetchPatched) {
          var origFetch = window.fetch.bind(window);
          window.fetch = function(input, init) {
            init = init || {};
            if (!init.credentials) init.credentials = 'include';
            return origFetch(input, init);
          };
          window.__aw_fetchPatched = true;
        }
        // Patch XHR to set withCredentials=true
        if (typeof window !== 'undefined' && window.XMLHttpRequest && !window.__aw_xhrPatched) {
          var p = window.XMLHttpRequest.prototype;
          var origOpen = p.open;
          var origSend = p.send;
          p.open = function() {
            try { this.withCredentials = true; } catch (e) {}
            return origOpen.apply(this, arguments);
          };
          p.send = function() {
            try { this.withCredentials = true; } catch (e) {}
            return origSend.apply(this, arguments);
          };
          window.__aw_xhrPatched = true;
        }
      }
    } catch (e) { /* noop */ }
  }
});
