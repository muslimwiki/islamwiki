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
            const elements = document.querySelectorAll('[z-data]');
            console.log('Found', elements.length, 'elements with z-data');
            elements.forEach((element, index) => {
                console.log('Processing element', index, element);
                this.initializeComponent(element);
            });
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
                    console.log('Reactive data keys:', Object.keys(reactiveData));
                } catch (e) {
                    console.error('Error parsing z-methods:', e);
                    console.error('Methods attribute was:', methodsAttr);
                    console.error('Error details:', e.message, e.stack);
                }
            }
            
            // Store component
            const componentId = 'component_' + Math.random().toString(36).substr(2, 9);
            this.components.set(componentId, {
                element,
                data: reactiveData
            });
            
            // Bind events
            this.bindComponentEvents(element, reactiveData);
            
            // Apply directives
            this.applyDirectives(element, reactiveData);
            
            console.log('Component initialized:', componentId, reactiveData);
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
            element.querySelectorAll('[z-click]').forEach(el => {
                console.log('Binding click event to:', el);
                const expression = el.getAttribute('z-click');
                console.log('Click expression:', expression);
                el.addEventListener('click', (e) => {
                    console.log('Click event fired, evaluating:', expression);
                    const result = this.evaluateExpression(expression, data, e);
                    console.log('Click result:', result);
                    // Update the component after the expression is evaluated
                    this.updateComponent(element, data);
                });
            });

            // Input events
            element.querySelectorAll('[z-model]').forEach(el => {
                console.log('Binding model event to:', el);
                const property = el.getAttribute('z-model');
                el.addEventListener('input', (e) => {
                    console.log('Input event, setting', property, 'to', e.target.value);
                    data[property] = e.target.value;
                    // Update the component after the data changes
                    this.updateComponent(element, data);
                });
            });
        }

        applyDirectives(element, data) {
            console.log('Applying directives to element:', element);
            
            // z-show directive
            element.querySelectorAll('[z-show]').forEach(el => {
                console.log('Applying z-show to:', el);
                const expression = el.getAttribute('z-show');
                console.log('Show expression:', expression);
                const isVisible = this.evaluateExpression(expression, data);
                console.log('Show result:', isVisible);
                if (isVisible) {
                    el.style.display = 'block';
                } else {
                    el.style.display = 'none';
                }
            });

            // z-text directive
            element.querySelectorAll('[z-text]').forEach(el => {
                console.log('Applying z-text to:', el);
                const expression = el.getAttribute('z-text');
                console.log('Text expression:', expression);
                const value = this.evaluateExpression(expression, data);
                console.log('Text result:', value);
                el.textContent = value;
            });

            // z-class directive
            element.querySelectorAll('[z-class]').forEach(el => {
                console.log('Applying z-class to:', el);
                const expression = el.getAttribute('z-class');
                console.log('Class expression:', expression);
                try {
                    // Parse the expression as a dynamic object
                    const classObject = {};
                    
                    // Extract class mappings from the expression
                    // Format: {"className": expression, "className2": expression2}
                    const matches = expression.match(/"([^"]+)":\s*([^,}]+)/g);
                    if (matches) {
                        // First, remove all classes that might be added by this directive
                        matches.forEach(match => {
                            const classMatch = match.match(/"([^"]+)":\s*([^,}]+)/);
                            if (classMatch) {
                                const className = classMatch[1];
                                el.classList.remove(className);
                                console.log('Removed class:', className);
                            }
                        });
                        
                        // Then add only the classes that should be active
                        // Make classes mutually exclusive - prioritize error over success
                        let hasActiveClass = false;
                        let hasErrorClass = false;
                        
                        // First pass: check if error should be active
                        for (const match of matches) {
                            const classMatch = match.match(/"([^"]+)":\s*([^,}]+)/);
                            if (classMatch) {
                                const className = classMatch[1];
                                const valueExpression = classMatch[2].trim();
                                
                                if (className === 'alert-error') {
                                    const value = this.evaluateExpression(valueExpression, data);
                                    console.log('Class', className, 'value:', value);
                                    if (value) {
                                        hasErrorClass = true;
                                        el.classList.add(className);
                                        console.log('Added class:', className);
                                        break; // Stop here, don't add success class
                                    }
                                }
                            }
                        }
                        
                        // Second pass: only add success if no error class was added
                        if (!hasErrorClass) {
                            for (const match of matches) {
                                const classMatch = match.match(/"([^"]+)":\s*([^,}]+)/);
                                if (classMatch) {
                                    const className = classMatch[1];
                                    const valueExpression = classMatch[2].trim();
                                    
                                    if (className === 'alert-success') {
                                        const value = this.evaluateExpression(valueExpression, data);
                                        console.log('Class', className, 'value:', value);
                                        if (value) {
                                            hasActiveClass = true;
                                            el.classList.add(className);
                                            console.log('Added class:', className);
                                            break; // Only add one success class
                                        }
                                    }
                                }
                            }
                        }
                    }
                } catch (e) {
                    console.error('Error parsing z-class expression:', e);
                    console.error('Expression was:', expression);
                }
            });
        }

        evaluateExpression(expression, data, event = null) {
            console.log('Evaluating expression:', expression);
            console.log('Data:', data);
            try {
                // Pass the reactive data directly, not a copy
                const context = data;
                console.log('Context before event:', context);
                if (event) {
                    context.$event = event;
                    context.$el = event.target;
                    console.log('Context after event:', context);
                }

                console.log('About to call safeEval with expression:', expression);
                const result = this.safeEval(expression, context);
                console.log('Expression result:', result);
                return result;
            } catch (e) {
                console.error('Error evaluating expression:', expression, e);
                console.error('Error stack:', e.stack);
                return null;
            }
        }

        safeEval(expression, context) {
            console.log('safeEval called with expression:', expression);
            console.log('safeEval context:', context);
            
            // Handle increment/decrement
            if (expression.includes('++')) {
                const varName = expression.replace('++', '').trim();
                console.log('Handling increment for:', varName);
                if (context[varName] !== undefined) {
                    context[varName]++;
                    console.log('Incremented', varName, 'to:', context[varName]);
                    return context[varName];
                }
            }
            
            if (expression.includes('--')) {
                const varName = expression.replace('--', '').trim();
                console.log('Handling decrement for:', varName);
                if (context[varName] !== undefined) {
                    context[varName]--;
                    console.log('Decremented', varName, 'to:', context[varName]);
                    return context[varName];
                }
            }

            // Handle assignment
            if (expression.includes('=')) {
                const parts = expression.split('=');
                const varName = parts[0].trim();
                const value = parts[1].trim();
                console.log('Handling assignment:', varName, '=', value);
                
                // Handle boolean values
                if (value === 'true') {
                    console.log('Setting', varName, 'to true');
                    context[varName] = true;
                    return true;
                }
                if (value === 'false') {
                    console.log('Setting', varName, 'to false');
                    context[varName] = false;
                    return false;
                }
                
                // Handle string values
                if (value.startsWith("'") && value.endsWith("'")) {
                    const stringValue = value.slice(1, -1);
                    console.log('Setting', varName, 'to string:', stringValue);
                    context[varName] = stringValue;
                    return context[varName];
                }
                if (value.startsWith('"') && value.endsWith('"')) {
                    const stringValue = value.slice(1, -1);
                    console.log('Setting', varName, 'to string:', stringValue);
                    context[varName] = stringValue;
                    return context[varName];
                }
                
                // Handle numbers
                if (!isNaN(value)) {
                    const numValue = parseFloat(value);
                    console.log('Setting', varName, 'to number:', numValue);
                    context[varName] = numValue;
                    return context[varName];
                }
                
                // Handle negation like "!show"
                if (value.startsWith('!')) {
                    const targetVar = value.substring(1);
                    console.log('Handling negation:', varName, '= !', targetVar);
                    if (context[targetVar] !== undefined) {
                        const newValue = !context[targetVar];
                        console.log('Setting', varName, 'to:', newValue, '(negation of', context[targetVar], ')');
                        context[varName] = newValue;
                        return newValue;
                    }
                }
                
                // Handle other variables
                if (context[value] !== undefined) {
                    console.log('Setting', varName, 'to value of', value, ':', context[value]);
                    context[varName] = context[value];
                    return context[varName];
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
                        console.log('Returning first truthy value:', context[part]);
                        return context[part];
                    }
                }
                console.log('Returning last part:', parts[parts.length - 1].trim());
                return parts[parts.length - 1].trim();
            }

            // Handle function calls like "showMessage('Hello')"
            if (expression.includes('(') && expression.includes(')')) {
                const match = expression.match(/^(\w+)\(([^)]*)\)$/);
                if (match) {
                    const functionName = match[1];
                    const argsString = match[2];
                    console.log('Handling function call:', functionName, 'with args:', argsString);
                    
                    if (typeof context[functionName] === 'function') {
                        // Parse arguments
                        const args = [];
                        if (argsString.trim()) {
                            // Simple argument parsing - split by comma and trim
                            argsString.split(',').forEach(arg => {
                                arg = arg.trim();
                                // Remove quotes if present
                                if ((arg.startsWith("'") && arg.endsWith("'")) || 
                                    (arg.startsWith('"') && arg.endsWith('"'))) {
                                    args.push(arg.slice(1, -1));
                                } else {
                                    args.push(arg);
                                }
                            });
                        }
                        
                        console.log('Calling function:', functionName, 'with args:', args);
                        const result = context[functionName](...args);
                        console.log('Function result:', result);
                        return result;
                    } else {
                        console.log('Function not found:', functionName);
                    }
                }
            }

            console.log('No matching pattern found for expression:', expression);
            return null;
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
            this.applyDirectives(element, data);
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