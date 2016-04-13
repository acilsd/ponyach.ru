function summon (srcs, cfg) {
"use strict"; var cbcount = 1; 
var callback = function () {
  --cbcount;
  if (cbcount === 0) {
     BrowserPonies.setBaseUrl(cfg.baseurl);
     if (!BrowserPoniesBaseConfig.loaded) {
         BrowserPonies.loadConfig(BrowserPoniesBaseConfig);
         BrowserPoniesBaseConfig.loaded = true;
     }
     BrowserPonies.loadConfig(cfg);
     if (!BrowserPonies.running()) {
         BrowserPonies.start();
     }
  }
}; 
if (typeof BrowserPoniesConfig === "undefined") { 
    window.BrowserPoniesConfig = {}; 
} 
if (typeof BrowserPoniesBaseConfig === "undefined") { 
    ++cbcount; 
    BrowserPoniesConfig.onbasecfg = callback; 
} 
if (typeof BrowserPonies === "undefined") 
{ 
    ++cbcount; 
    BrowserPoniesConfig.oninit = callback; 
} 
var node = document.body || document.documentElement || document.getElementsByTagName("head")[0]; 
for (var id in srcs) { 
    if (document.getElementById(id)) {
          continue; 
    } 
    if (node) {
          var s = document.createElement("script"); 
          s.type = "text/javascript"; 
          s.id = id; 
          s.src = srcs[id]; 
          node.appendChild(s);
    } else { 
          document.write("<script type=\"text/javscript\" src=\"" + srcs[id] + "\" id=\"" + id + "\"></script>"); 
    } 
} 
callback();
}



function summon_rar () {
summon({"browser-ponies-script":"http://panzi.github.com/Browser-Ponies/browserponies.js",
"browser-ponies-config":"http://panzi.github.com/Browser-Ponies/basecfg.js"},{"fadeDuration":500,"volume":1,"fps":25,"speed":3,"audioEnabled":false,"showFps":false,"showLoadProgress":true,"speakProbability":0.1,"baseurl":"http://panzi.github.com/Browser-Ponies/","spawn":{"rarity":1}});
}

function summon_flu () {
summon({"browser-ponies-script":"http://panzi.github.com/Browser-Ponies/browserponies.js",
"browser-ponies-config":"http://panzi.github.com/Browser-Ponies/basecfg.js"},{"fadeDuration":500,"volume":1,"fps":25,"speed":3,"audioEnabled":false,"showFps":false,"showLoadProgress":true,"speakProbability":0.1,"baseurl":"http://panzi.github.com/Browser-Ponies/","spawn":{"fluttershy":1}});
}

function summon_twi() {
summon({"browser-ponies-script":"http://panzi.github.com/Browser-Ponies/browserponies.js",
"browser-ponies-config":"http://panzi.github.com/Browser-Ponies/basecfg.js"},{"fadeDuration":500,"volume":1,"fps":25,"speed":3,"audioEnabled":false,"showFps":false,"showLoadProgress":true,"speakProbability":0.1,"baseurl":"http://panzi.github.com/Browser-Ponies/","spawn":{"twilight sparkle":1}});
}

function summon_apl () {
summon({"browser-ponies-script":"http://panzi.github.com/Browser-Ponies/browserponies.js",
"browser-ponies-config":"http://panzi.github.com/Browser-Ponies/basecfg.js"},{"fadeDuration":500,"volume":1,"fps":25,"speed":3,"audioEnabled":false,"showFps":false,"showLoadProgress":true,"speakProbability":0.1,"baseurl":"http://panzi.github.com/Browser-Ponies/","spawn":{"applejack":1}});
}

function summon_pin () {
summon({"browser-ponies-script":"http://panzi.github.com/Browser-Ponies/browserponies.js",
"browser-ponies-config":"http://panzi.github.com/Browser-Ponies/basecfg.js"},{"fadeDuration":500,"volume":1,"fps":25,"speed":3,"audioEnabled":false,"showFps":false,"showLoadProgress":true,"speakProbability":0.1,"baseurl":"http://panzi.github.com/Browser-Ponies/","spawn":{"pinkie pie":1}});
}

function summon_rai () {
summon({"browser-ponies-script":"http://panzi.github.com/Browser-Ponies/browserponies.js",
"browser-ponies-config":"http://panzi.github.com/Browser-Ponies/basecfg.js"},{"fadeDuration":500,"volume":1,"fps":25,"speed":3,"audioEnabled":false,"showFps":false,"showLoadProgress":true,"speakProbability":0.1,"baseurl":"http://panzi.github.com/Browser-Ponies/","spawn":{"rainbow dash":1}});
}

function summon_cel () {
summon({"browser-ponies-script":"http://panzi.github.com/Browser-Ponies/browserponies.js",
"browser-ponies-config":"http://panzi.github.com/Browser-Ponies/basecfg.js"},{"fadeDuration":500,"volume":1,"fps":25,"speed":3,"audioEnabled":false,"showFps":false,"showLoadProgress":true,"speakProbability":0.1,"baseurl":"http://panzi.github.com/Browser-Ponies/","spawn":{"princess celestia":1}});
}

function summon_lun () {
summon({"browser-ponies-script":"http://panzi.github.com/Browser-Ponies/browserponies.js",
"browser-ponies-config":"http://panzi.github.com/Browser-Ponies/basecfg.js"},{"fadeDuration":500,"volume":1,"fps":25,"speed":3,"audioEnabled":false,"showFps":false,"showLoadProgress":true,"speakProbability":0.1,"baseurl":"http://panzi.github.com/Browser-Ponies/","spawn":{"princess luna":1}});
}

function summon_swi () {
summon({"browser-ponies-script":"http://panzi.github.com/Browser-Ponies/browserponies.js",
"browser-ponies-config":"http://panzi.github.com/Browser-Ponies/basecfg.js"},{"fadeDuration":500,"volume":1,"fps":25,"speed":3,"audioEnabled":false,"showFps":false,"showLoadProgress":true,"speakProbability":0.1,"baseurl":"http://panzi.github.com/Browser-Ponies/","spawn":{"sweetie belle":1}});
}

function summon_sco () {
summon({"browser-ponies-script":"http://panzi.github.com/Browser-Ponies/browserponies.js",
"browser-ponies-config":"http://panzi.github.com/Browser-Ponies/basecfg.js"},{"fadeDuration":500,"volume":1,"fps":25,"speed":3,"audioEnabled":false,"showFps":false,"showLoadProgress":true,"speakProbability":0.1,"baseurl":"http://panzi.github.com/Browser-Ponies/","spawn":{"scootaloo":1}});
}

function summon_apb () {
summon({"browser-ponies-script":"http://panzi.github.com/Browser-Ponies/browserponies.js",
"browser-ponies-config":"http://panzi.github.com/Browser-Ponies/basecfg.js"},{"fadeDuration":500,"volume":1,"fps":25,"speed":3,"audioEnabled":false,"showFps":false,"showLoadProgress":true,"speakProbability":0.1,"baseurl":"http://panzi.github.com/Browser-Ponies/","spawn":{"apple bloom":1}});
}

function summon_nim () {
summon({"browser-ponies-script":"http://panzi.github.com/Browser-Ponies/browserponies.js",
"browser-ponies-config":"http://panzi.github.com/Browser-Ponies/basecfg.js"},{"fadeDuration":500,"volume":1,"fps":25,"speed":3,"audioEnabled":false,"showFps":false,"showLoadProgress":true,"speakProbability":0.1,"baseurl":"http://panzi.github.com/Browser-Ponies/","spawn":{"nightmare moon":1}});
}

function summon_cad () {
summon({"browser-ponies-script":"http://panzi.github.com/Browser-Ponies/browserponies.js",
"browser-ponies-config":"http://panzi.github.com/Browser-Ponies/basecfg.js"},{"fadeDuration":500,"volume":1,"fps":25,"speed":3,"audioEnabled":false,"showFps":false,"showLoadProgress":true,"speakProbability":0.1,"baseurl":"http://panzi.github.com/Browser-Ponies/","spawn":{"princess cadance":1}});
}

function summon_chr () {
summon({"browser-ponies-script":"http://panzi.github.com/Browser-Ponies/browserponies.js",
"browser-ponies-config":"http://panzi.github.com/Browser-Ponies/basecfg.js"},{"fadeDuration":500,"volume":1,"fps":25,"speed":3,"audioEnabled":false,"showFps":false,"showLoadProgress":true,"speakProbability":0.1,"baseurl":"http://panzi.github.com/Browser-Ponies/","spawn":{"queen chrysalis":1}});
}

function summon_ln2 () {
summon({"browser-ponies-script":"http://panzi.github.com/Browser-Ponies/browserponies.js",
"browser-ponies-config":"http://panzi.github.com/Browser-Ponies/basecfg.js"},{"fadeDuration":500,"volume":1,"fps":25,"speed":3,"audioEnabled":false,"showFps":false,"showLoadProgress":true,"speakProbability":0.1,"baseurl":"http://panzi.github.com/Browser-Ponies/","spawn":{"princess luna (season 1)":1}});
}

function summon_trx () {
summon({"browser-ponies-script":"http://panzi.github.com/Browser-Ponies/browserponies.js",
"browser-ponies-config":"http://panzi.github.com/Browser-Ponies/basecfg.js"},{"fadeDuration":500,"volume":1,"fps":25,"speed":3,"audioEnabled":false,"showFps":false,"showLoadProgress":true,"speakProbability":0.1,"baseurl":"http://panzi.github.com/Browser-Ponies/","spawn":{"trixie":1}});
}

function sound_enable (){
BrowserPonies.setAudioEnabled(true); 
}

function sound_disable (){
BrowserPonies.setAudioEnabled(false); 
}

