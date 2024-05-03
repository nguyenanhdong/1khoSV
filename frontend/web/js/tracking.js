var Interactor = function (endpoint, config) {
    // Call Initialization on Interactor Call
    this.__init__(endpoint, config);
};
function Generator() {};

Generator.prototype.rand =  Math.floor(Math.random() * 100) + Date.now();

Generator.prototype.getId = function() {
   return this.rand++;
};
Interactor.prototype = {

    // Initialization
    __init__: function (endpoint, config) {

        var interactor = this;
        
        // Argument Assignment          // Type Checks                                                                          // Default Values
        interactor.interactions       = typeof(config.interactions)               == "boolean"    ? config.interactions        : true,
        interactor.interactionElement = typeof(config.interactionElement)         == "string"     ? config.interactionElement :'interaction',
        interactor.interactionEvents  = Array.isArray(config.interactionEvents)   === true        ? config.interactionEvents  : ['mouseup', 'touchend'],
        interactor.conversions        = typeof(config.conversions)                == "boolean"    ? config.conversions        : true,
        interactor.conversionElement  = typeof(config.conversionElement)          == "string"     ? config.conversionElement  : 'conversion',
        interactor.conversionEvents   = Array.isArray(config.conversionEvents)    === true        ? config.conversionEvents   : ['mouseup', 'touchend'],
        interactor.endpoint           = typeof(endpoint)                          == "string"     ? endpoint                  : '/tracking.html',
        interactor.debug              = typeof(config.debug)                      == "boolean"    ? config.debug              : true,
        interactor.records            = [],
        interactor.session            = {},
        interactor.loadTime           = new Date();
        
        // Initialize Session
        interactor.__initializeSession__(config);
        // Call Event Binding Method
        interactor.__bindEvents__();
        
        return interactor;
    },

    // Create Events to Track
    __bindEvents__: function () {
        
        var interactor  = this;

        // Set Interaction Capture
        if (interactor.interactions === true) {
            for (var i = 0; i < interactor.interactionEvents.length; i++) {
                document.querySelector('body').addEventListener(interactor.interactionEvents[i], function (e) {
                    e.stopPropagation();
                    if (e.target.classList.value === interactor.interactionElement) {
                        interactor.__addInteraction__(e, "interaction");
                    }
                });
            }   
        }

        // Set Conversion Capture
        if (interactor.conversions === true) {
            for (var i = 0; i < interactor.conversionEvents.length; i++) {
                document.querySelector('body').addEventListener(interactor.conversionEvents[i], function (e) {
                    e.stopPropagation();
                    if (e.target.classList.value === interactor.conversionElement) {
                        interactor.__addInteraction__(e, "conversion");
                    }
                });
            }   
        }

        // Bind onbeforeunload Event
        window.onbeforeunload = function (e) {
            interactor.__sendInteractions__();
        };
        
        return interactor;
    },

    // Add Interaction Object Triggered By Events to Records Array
    __addInteraction__: function (e, type) {
            
        var interactor  = this,

            // Interaction Object
            interaction     = {
                type            : type,
                event           : e.type,
                targetTag       : e.target.nodeName,
                targetClasses   : e.target.className,
                content         : e.target.innerText,
                clientPosition  : {
                    x               : e.clientX,
                    y               : e.clientY
                },
                screenPosition  : {
                    x               : e.screenX,
                    y               : e.screenY
                },
                createdAt       : new Date()
            };
        
        // Insert into Records Array
        interactor.records.push(interaction);

        // Log Interaction if Debugging
        if (interactor.debug) {
            // Close Session & Log to Console
            interactor.__closeSession__();
            console.log("Session:\n", interactor.session);
        }

        return interactor;
    },
    // Generate Session Object & Assign to Session Property
    __initializeSession__: function (config) {
        var interactor = this;
        setTimeout(function(){
            var idGen      = new Generator();
            var uqid       = idGen.getId();
            // Assign Session Property
            interactor.session              = config;
            interactor.session.uqid         = uqid.toString().substr(-8);
            interactor.session.loadTime     = interactor.loadTime;
            interactor.session.unloadTime   = new Date();
            interactor.session.init         = 1;
            interactor.session.url          = window.location.href;
            $.post(interactor.endpoint,interactor.session,function(){});
        },300);
        return interactor;
    },

    // Insert End of Session Values into Session Property
    __closeSession__: function () {

        var interactor = this;

        // Assign Session Properties
        interactor.session.unloadTime   = new Date();
        interactor.session.interactions = interactor.records;
        interactor.session.init         = 0;

        return interactor;
    },


    // Gather Additional Data and Send Interaction(s) to Server
    __sendInteractions__: function () {
        
        var interactor  = this;
            // Initialize Cross Header Request
            
        // Close Session
        interactor.__closeSession__();
        // $.post(interactor.endpoint,interactor.session,function(){});
        
        return interactor;
    }

};
