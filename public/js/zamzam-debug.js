/**
 * ZamZam.js Debug Version
 * Simplified version with console logging
 */

console.log('ZamZam.js loading...');

(function() {
    'use strict';

    console.log('ZamZam.js initializing...');

    // Simple ZamZam implementation
    class ZamZam {
        constructor() {
            console.log('ZamZam constructor called');
            this.components = new Map();
            this.init();
        }

        init() {
            console.log('ZamZam init called');
            if (document.readyState === 'loading') {
                console.log('DOM loading, adding event listener');
                document.addEventListener('DOMContentLoaded', () => this.start());
            } else {
                console.log('DOM already loaded, starting immediately');
                this.start();
            }
        }

        start() {
            console.log('ZamZam start called');
            this.scanForComponents();
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

            // Create reactive data
            const reactiveData = this.makeReactive(data);
            
            // Bind events
            this.bindComponentEvents(element, reactiveData);
            
            // Apply directives
            this.applyDirectives(element, reactiveData);
        }

        makeReactive(data) {
            console.log('Making data reactive:', data);
            const self = this;
            return new Proxy(data, {
                get(target, key) {
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
                    this.evaluateExpression(expression, data, e);
                });
            });

            // Click away events
            element.querySelectorAll('[z-click-away]').forEach(el => {
                console.log('Binding click-away event to:', el);
                const expression = el.getAttribute('z-click-away');
                document.addEventListener('click', (e) => {
                    if (!el.contains(e.target)) {
                        console.log('Click away event fired');
                        this.evaluateExpression(expression, data, e);
                    }
                });
            });

            // Input events
            element.querySelectorAll('[z-model]').forEach(el => {
                console.log('Binding model event to:', el);
                const property = el.getAttribute('z-model');
                el.addEventListener('input', (e) => {
                    console.log('Input event, setting', property, 'to', e.target.value);
                    data[property] = e.target.value;
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
                el.style.display = isVisible ? '' : 'none';
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
                const classes = this.evaluateExpression(expression, data);
                console.log('Class result:', classes);
                if (typeof classes === 'object') {
                    Object.keys(classes).forEach(className => {
                        if (classes[className]) {
                            el.classList.add(className);
                        } else {
                            el.classList.remove(className);
                        }
                    });
                }
            });
        }

        evaluateExpression(expression, data, event = null) {
            console.log('Evaluating expression:', expression);
            console.log('Data:', data);
            try {
                const context = { ...data };
                if (event) {
                    context.$event = event;
                    context.$el = event.target;
                }

                // Create function from expression
                const func = new Function(...Object.keys(context), `return ${expression}`);
                const result = func(...Object.values(context));
                console.log('Expression result:', result);
                return result;
            } catch (e) {
                console.error('Error evaluating expression:', expression, e);
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
            this.applyDirectives(element, data);
        }
    }

    // Initialize ZamZam
    console.log('Creating ZamZam instance...');
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            console.log('DOM loaded, starting ZamZam');
            window.ZamZamInstance = new ZamZam();
        });
    } else {
        console.log('DOM already loaded, starting ZamZam immediately');
        window.ZamZamInstance = new ZamZam();
    }

    // Expose to global scope
    window.ZamZam = ZamZam;
    console.log('ZamZam.js loaded successfully');

})(); 