/*
 Copyright (c) 2005-2006 VK schools_ring_at_yahoo.com
 Permission is hereby granted, free of charge, to
 any person obtaining a copy of this software and
 associated documentation files (the "Software"), to
 deal in the Software without restriction, including
 without limitation the rights to use, copy, modify,
 merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom
 the Software is furnished to do so, subject to the
 following conditions:
 The above copyright notice and this permission notice
 shall be included in all copies or substantial portions
 of the Software.
 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY
 OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT
 LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE,
 ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/ 

var Shell = {
  '$err'  : function(m) {
   var msg = m || 'Security exceptions';
   window.alert('[Shell] script object\n\n' + msg);
  }
, 'MSIE'  : (  (typeof window != 'undefined')
            && (typeof window.ActiveXObject != 'undefined'))
, 'Gecko' : (  (typeof window != 'undefined')
            && (typeof window.netscape != 'undefined')
            && (typeof window.netscape.security != 'undefined')
            /* that Opera... always pretending to do everything
             * everywhere but not really doing anything of it...
             */
            && (typeof window.opera != 'object'))
, 'run'   : function(path, args) {
   if ((typeof path == 'string') && (path != '')) {
    if ((Shell.MSIE) && (typeof Shell.$ == 'undefined')) {
     /* If first time, try to instantiate ActiveX object
      * for shell access:
      */
     try {
      Shell.$ = new ActiveXObject('WScript.Shell');
     }
     catch(e) {
      Shell.$err(e.message);
      return null;
     }
    }
    /*
     */
    if (Shell.MSIE) {
     var arg = ((typeof args == 'string') && (args != '')) ?
     (' "' + args + '"') : '';
     var exe = '"'.concat(path, '"', arg);
     try {
      Shell.$.Run(exe);
     }
     catch(e) {
      Shell.$err(e.message);
     }
    }
    else if (Shell.Gecko) {
     /* Netscape security model grants privileges
      * on the per-call per-context basis; thus
      * privilege request and privilege usage
      * have to be in the same block.
      */
     try {
      netscape.security.PrivilegeManager.enablePrivilege('UniversalXPConnect');
      Shell.$ = Components.classes['@mozilla.org/file/local;1'].createInstance(Components.interfaces.nsILocalFile);
      Shell.$.initWithPath(path);
      if ((typeof args == 'string') && (args != '')) {
       Shell._ = Components.classes['@mozilla.org/process/util;1'].
       createInstance(Components.interfaces.nsIProcess);
       Shell._.init(Shell.$);
       Shell._.run(false, [args], 1);
      }
      else {
       Shell.$.launch();
      }
     }
     catch(e) {
      Shell.$err(e);
     }
    }
    else {
     Shell.$err('not supported on this platform');
    }
   }
   else {
    Shell.$err('Invalid argument');
   }
  }
 };
