/**
 * ZamZam.js - A lightweight JavaScript framework for IslamWiki
 * Inspired by Alpine.js but custom-built for Islamic applications
 */

console.log('ZamZam.js loading...');

(function() {
    'use strict';

    class ZamZam {
        constructor() {
            console.log('ZamZam constructor called');
            this.components = new Map();
            this.init();
        }

        init() {
            console.log('ZamZam init called');
            // Always wait for DOM to be completely ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => this.start());
            } else {
                // If DOM is already loaded, wait a bit to ensure all elements are available
                setTimeout(() => this.start(), 100);
            }
        }

        start() {
            console.log('ZamZam start called');
            // Double-check that DOM is ready
            if (document.readyState === 'complete') {
                this.scanForComponents();
            } else {
                window.addEventListener('load', () => this.scanForComponents());
            }
        }

        scanForComponents() {
            console.log('Scanning for components...');
            
            // First, hide all z-show elements to prevent flash
            document.querySelectorAll('[z-show]').forEach(el => {
                if (!el.classList.contains('zamzam-processed')) {
                    el.style.display = 'none';
                    el.style.visibility = 'hidden';
                    el.style.opacity = '0';
                    console.log('Hidden element initially:', el);
                }
            });
            
            const elements = document.querySelectorAll('[z-data]');
            console.log('Found', elements.length, 'elements with z-data');
            elements.forEach((element, index) => {
                console.log('Processing element', index, element);
                this.initializeComponent(element);
            });
            
            // After all components are initialized, show elements that should be visible
            setTimeout(() => {
                document.querySelectorAll('[z-show].zamzam-processed').forEach(el => {
                    const expression = el.getAttribute('z-show');
                    const isVisible = this.evaluateExpression(expression, this.components.get(el.closest('[z-data]')));
                    if (isVisible) {
                        el.style.display = '';
                        el.style.visibility = 'visible';
                        el.style.opacity = '1';
                    }
                });
            }, 100);
        }

        initializeComponent(element) {
            console.log('Initializing component:', element);
            const dataAttr = element.getAttribute('z-data');
            console.log('Data attribute:', dataAttr);
            
            let data = {};
            try {
                if (dataAttr.startsWith('{')) {
                    data = JSON.parse(dataAttr);
                } else {
                    data = { value: dataAttr };
                }
                console.log('Parsed data:', data);
            } catch (e) {
                console.error('Error parsing z-data:', e);
                data = {};
            }

            // Create reactive data first
            const reactiveData = this.makeReactive(data);
            
            // Parse methods if present and add to reactive data
            const methodsAttr = element.getAttribute('z-methods');
            if (methodsAttr) {
                console.log('Methods attribute:', methodsAttr);
                try {
                    const methods = JSON.parse(methodsAttr);
                    console.log('Parsed methods:', methods);
                    
                    // Add methods to reactive data
                    Object.keys(methods).forEach(methodName => {
                        let methodBody = methods[methodName];
                        console.log('Adding method:', methodName, methodBody);
                        
                        try {
                            // Strip function keyword if present
                            if (methodBody.startsWith('function(')) {
                                methodBody = methodBody.replace(/^function\s*\([^)]*\)\s*/, '');
                                console.log('Stripped function keyword, new body:', methodBody);
                            }
                            
                            // Create function from method body and bind to reactive data
                            const methodFunction = new Function('msg', methodBody);
                            reactiveData[methodName] = methodFunction.bind(reactiveData);
                            console.log('Method added to reactive data:', methodName, typeof reactiveData[methodName]);
                        } catch (methodError) {
                            console.error('Error creating method', methodName, ':', methodError);
                            console.error('Method body was:', methodBody);
                        }
                    });
                    
                    console.log('Reactive data after adding methods:', reactiveData);
                } catch (methodParseError) {
                    console.error('Error parsing methods:', methodParseError);
                }
            }

            // Store the component data
            this.components.set(element, reactiveData);
            
            // Bind events first
            this.bindComponentEvents(element, reactiveData);
            
            // Then apply directives
            this.applyDirectives(element, reactiveData);
            
            console.log('Component initialized successfully:', element);
        }

        makeReactive(data) {
            console.log('Making data reactive:', data);
            const self = this;
            return new Proxy(data, {
                get(target, key) {
                    console.log('Getting', key, ':', target[key]);
                    return target[key];
                },
                set(target, key, value) {
                    console.log('Setting', key, 'to', value);
                    const oldValue = target[key];
                    target[key] = value;
                    
                    if (oldValue !== value) {
                        console.log('Value changed, triggering reactivity');
                        self.triggerReactivity(target, key, value);
                    }
                    return true;
                }
            });
        }

        bindComponentEvents(element, data) {
            console.log('Binding events for element:', element);
            
            // Click events
            const clickElements = element.querySelectorAll('[z-click]');
            console.log('Found', clickElements.length, 'elements with z-click');
            clickElements.forEach((el, index) => {
                console.log(`Binding click event ${index} to:`, el);
                const expression = el.getAttribute('z-click');
                console.log('Click expression:', expression);
                el.addEventListener('click', (e) => {
                    console.log('Click event fired on element:', el);
                    console.log('Click event fired, evaluating:', expression);
                    e.preventDefault();
                    e.stopPropagation();
                    const result = this.evaluateExpression(expression, data, e);
                    console.log('Click result:', result);
                    // Update the component after the expression is evaluated
                    this.updateComponent(element, data);
                });
                console.log('Click event bound successfully to:', el);
            });

            // Click away events
            const clickAwayElements = element.querySelectorAll('[z-click-away]');
            console.log('Found', clickAwayElements.length, 'elements with z-click-away');
            clickAwayElements.forEach((el, index) => {
                console.log(`Binding click-away event ${index} to:`, el);
                const expression = el.getAttribute('z-click-away');
                console.log('Click-away expression:', expression);
                
                document.addEventListener('click', (e) => {
                    if (!element.contains(e.target)) {
                        console.log('Click away detected on element:', el);
                        console.log('Click away detected, evaluating:', expression);
                        const result = this.evaluateExpression(expression, data, e);
                        console.log('Click-away result:', result);
                        this.updateComponent(element, data);
                    }
                });
                console.log('Click-away event bound successfully to:', el);
            });

            // Input events
            const modelElements = element.querySelectorAll('[z-model]');
            console.log('Found', modelElements.length, 'elements with z-model');
            modelElements.forEach((el, index) => {
                console.log(`Binding model event ${index} to:`, el);
                const property = el.getAttribute('z-model');
                el.addEventListener('input', (e) => {
                    console.log('Input event, setting', property, 'to', e.target.value);
                    data[property] = e.target.value;
                    // Update the component after the data changes
                    this.updateComponent(element, data);
                });
                console.log('Model event bound successfully to:', el);
            });
        }

        applyDirectives(element, data) {
            console.log('Applying directives to element:', element);
            
            // z-show directive
            element.querySelectorAll('[z-show]').forEach(el => {
                console.log('Applying z-show to:', el);
                // Always read the original expression from the HTML, never modify the attribute
                const expression = el.getAttribute('z-show');
                console.log('Show expression:', expression);
                const isVisible = this.evaluateExpression(expression, data);
                console.log('Show result:', isVisible);
                
                if (isVisible) {
                    el.style.display = '';
                    el.style.visibility = 'visible';
                    el.style.opacity = '1';
                } else {
                    el.style.display = 'none';
                    el.style.visibility = 'hidden';
                    el.style.opacity = '0';
                }
                
                // Mark as processed
                el.classList.add('zamzam-processed');
            });

            // z-text directive
            element.querySelectorAll('[z-text]').forEach(el => {
                console.log('Applying z-text to:', el);
                const expression = el.getAttribute('z-text');
                console.log('Text expression:', expression);
                const value = this.evaluateExpression(expression, data);
                console.log('Text result:', value);
                el.textContent = value || '';
            });

            // z-html directive
            element.querySelectorAll('[z-html]').forEach(el => {
                console.log('Applying z-html to:', el);
                const expression = el.getAttribute('z-html');
                console.log('HTML expression:', expression);
                const value = this.evaluateExpression(expression, data);
                console.log('HTML result:', value);
                el.innerHTML = value || '';
            });

            // z-class directive
            element.querySelectorAll('[z-class]').forEach(el => {
                console.log('Applying z-class to:', el);
                const expression = el.getAttribute('z-class');
                console.log('Class expression:', expression);
                const value = this.evaluateExpression(expression, data);
                console.log('Class result:', value);
                
                if (value) {
                    el.classList.add(value);
                }
            });

            // z-bind directive
            element.querySelectorAll('[z-bind]').forEach(el => {
                console.log('Applying z-bind to:', el);
                const expression = el.getAttribute('z-bind');
                console.log('Bind expression:', expression);
                
                // Parse the expression like "src: imageUrl"
                const parts = expression.split(':');
                if (parts.length === 2) {
                    const attribute = parts[0].trim();
                    const property = parts[1].trim();
                    const value = this.evaluateExpression(property, data);
                    console.log('Bind result:', attribute, '=', value);
                    el.setAttribute(attribute, value || '');
                }
            });
        }

        evaluateExpression(expression, data, event = null) {
            console.log('Evaluating expression:', expression, 'with data:', data);
            
            const context = data;
            
            // Handle simple boolean expressions
            if (expression === 'true') return true;
            if (expression === 'false') return false;
            
            // Handle negation expressions like "!open"
            if (expression.startsWith('!')) {
                const property = expression.substring(1);
                if (context[property] !== undefined) {
                    console.log('Handling negation:', property, '=', !context[property]);
                    return !context[property];
                }
            }
            
            // Handle simple property access
            if (context[expression] !== undefined) {
                console.log('Getting', expression, ':', context[expression]);
                return context[expression];
            }
            
            // Handle logical expressions like "name || 'Guest'"
            if (expression.includes('||')) {
                const parts = expression.split('||');
                console.log('Handling logical OR:', parts);
                for (let part of parts) {
                    part = part.trim();
                    if (context[part] !== undefined && context[part]) {
                        return context[part];
                    }
                }
                return parts[parts.length - 1].trim().replace(/['"]/g, '');
            }
            
            // Handle function calls like "toggleOpen()"
            if (expression.includes('(') && expression.includes(')')) {
                const functionName = expression.split('(')[0];
                if (context[functionName] && typeof context[functionName] === 'function') {
                    console.log('Calling function:', functionName);
                    const result = context[functionName]();
                    console.log('Function result:', result);
                    return result;
                } else {
                    console.log('Function not found:', functionName);
                }
            }
            
            // Handle assignments like "open = !open" or "open = true"
            if (expression.includes('=')) {
                const parts = expression.split('=');
                if (parts.length === 2) {
                    const property = parts[0].trim();
                    const valueExpression = parts[1].trim();
                    console.log('Handling assignment:', property, '=', valueExpression);
                    
                    // Evaluate the right side of the assignment
                    let newValue;
                    if (valueExpression === 'true') {
                        newValue = true;
                    } else if (valueExpression === 'false') {
                        newValue = false;
                    } else if (valueExpression.startsWith('!')) {
                        // Handle negation like "!open"
                        const negatedProperty = valueExpression.substring(1);
                        newValue = !context[negatedProperty];
                    } else {
                        // Try to evaluate as a simple expression
                        try {
                            newValue = this.safeEval(valueExpression, context);
                        } catch (e) {
                            console.error('Error evaluating expression:', e);
                            newValue = false;
                        }
                    }
                    
                    // Update the property
                    context[property] = newValue;
                    console.log('Updated', property, 'to:', newValue);
                    return newValue;
                }
            }

            console.log('No matching pattern found for expression:', expression);
            return null;
        }

        safeEval(expression, context) {
            console.log('Safe eval:', expression, 'with context:', context);
            
            try {
                // Create a safe evaluation environment
                const safeContext = {};
                Object.keys(context).forEach(key => {
                    if (typeof context[key] !== 'function') {
                        safeContext[key] = context[key];
                    }
                });
                
                // Use Function constructor for safer evaluation
                const func = new Function(...Object.keys(safeContext), `return ${expression}`);
                const result = func(...Object.values(safeContext));
                console.log('Safe eval result:', result);
                return result;
            } catch (e) {
                console.error('Safe eval error:', e);
                return null;
            }
        }

        triggerReactivity(data, key, value) {
            console.log('Triggering reactivity for:', key, '=', value);
            this.components.forEach((component, id) => {
                if (component.data === data) {
                    console.log('Updating component:', id);
                    this.updateComponent(component.element, component.data);
                }
            });
        }

        updateComponent(element, data) {
            console.log('Updating component:', element);
            console.log('Component data:', data);
            this.applyDirectives(element, data);
            console.log('Component updated successfully');
        }
    }

    // Initialize ZamZam
    console.log('Creating ZamZam instance...');
    function initializeZamZam() {
        console.log('Initializing ZamZam...');
        if (!window.ZamZamInstance) {
            window.ZamZamInstance = new ZamZam();
        }
    }

    // Always wait for DOM to be ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeZamZam);
    } else {
        // If DOM is already loaded, wait a bit to ensure all elements are available
        setTimeout(initializeZamZam, 100);
    }

    // Expose to global scope
    window.ZamZam = ZamZam;
    console.log('ZamZam.js loaded successfully');

})(); 