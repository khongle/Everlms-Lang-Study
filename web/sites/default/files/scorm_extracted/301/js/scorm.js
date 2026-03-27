var pipwerks = pipwerks || {};
pipwerks.SCORM = {
    version: "1.2",
    handleCompletionStatus: true,
    handleExitMode: true,
    API: { handle: null, isFound: false },
    connection: { isActive: false },
    data: { completionStatus: null, exitStatus: null },
    debug: { isActive: true, displayInfo: true },
    init: function() {
        pipwerks.SCORM.API.handle = pipwerks.SCORM.API.get();
        if (pipwerks.SCORM.API.handle) {
            var callSucceeded = pipwerks.SCORM.API.handle.LMSInitialize("");
            if (callSucceeded === "true" || callSucceeded === true) {
                pipwerks.SCORM.connection.isActive = true;
                return true;
            }
        }
        return false;
    },
    get: function(parameter) {
        if (pipwerks.SCORM.connection.isActive) {
            return pipwerks.SCORM.API.handle.LMSGetValue(parameter);
        }
        return null;
    },
    set: function(parameter, value) {
        if (pipwerks.SCORM.connection.isActive) {
            var callSucceeded = pipwerks.SCORM.API.handle.LMSSetValue(parameter, value);
            return (callSucceeded === "true" || callSucceeded === true);
        }
        return false;
    },
    save: function() {
        if (pipwerks.SCORM.connection.isActive) {
            var callSucceeded = pipwerks.SCORM.API.handle.LMSCommit("");
            return (callSucceeded === "true" || callSucceeded === true);
        }
        return false;
    },
    quit: function() {
        if (pipwerks.SCORM.connection.isActive) {
            pipwerks.SCORM.API.handle.LMSFinish("");
            pipwerks.SCORM.connection.isActive = false;
            return true;
        }
        return false;
    },
    API: {
        get: function() {
            var api = pipwerks.SCORM.API.find(window);
            if (!api && window.parent && window.parent != window) {
                api = pipwerks.SCORM.API.find(window.parent);
            }
            if (!api && window.top && window.top.opener) {
                api = pipwerks.SCORM.API.find(window.top.opener);
            }
            return api;
        },
        find: function(win) {
            var findAttempts = 0, findAttemptLimit = 500, winAPI = win.API;
            while ((!win.API) && (win.parent) && (win.parent != win) && (findAttempts <= findAttemptLimit)) {
                findAttempts++;
                win = win.parent;
            }
            return win.API;
        }
    }
};