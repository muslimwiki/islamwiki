<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Safa CSS Framework Test - IslamWiki</title>
    
    <!-- Google Fonts for Islamic Typography -->
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Noto+Naskh+Arabic:wght@400;700&display=swap" rel="stylesheet">
    
    <!-- Safa CSS Framework -->
    <link rel="stylesheet" href="/css/safa.css">
    
    <style>
        :root {
            --primary-color: #2E7D32;
            --secondary-color: #4CAF50;
            --accent-color: #81C784;
            --background-color: #F1F8E9;
            --text-color: #1B5E20;
            --border-color: #4CAF50;
            --link-color: #2E7D32;
            --link-hover-color: #1B5E20;
        }
    </style>
</head>
<body>
    <div class="header bg-primary text-white p-4">
        <div class="container">
            <div class="d-flex justify-between align-center">
                <h1 class="text-white">📚 Safa CSS Framework Test</h1>
                <nav class="d-flex">
                    <a href="#" class="text-white m-2">Home</a>
                    <a href="#" class="text-white m-2">About</a>
                    <a href="#" class="text-white m-2">Contact</a>
                </nav>
            </div>
        </div>
    </div>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h2 class="text-primary">Safa CSS Framework - صفاء</h2>
                <p class="text-secondary">A lightweight, pure CSS framework for IslamWiki</p>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title">Buttons</h3>
                    </div>
                    <div class="card-body">
                        <button class="btn btn-primary m-1">Primary</button>
                        <button class="btn btn-secondary m-1">Secondary</button>
                        <button class="btn btn-success m-1">Success</button>
                        <button class="btn btn-danger m-1">Danger</button>
                        <button class="btn btn-warning m-1">Warning</button>
                        <button class="btn btn-info m-1">Info</button>
                        <br><br>
                        <button class="btn btn-outline-primary m-1">Outline Primary</button>
                        <button class="btn btn-sm btn-primary m-1">Small</button>
                        <button class="btn btn-lg btn-primary m-1">Large</button>
                    </div>
                </div>
            </div>
            
            <div class="col-6">
                <div class="card shadow">
                    <div class="card-header bg-secondary text-white">
                        <h3 class="card-title">Alerts</h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-primary">Primary alert</div>
                        <div class="alert alert-success">Success alert</div>
                        <div class="alert alert-danger">Danger alert</div>
                        <div class="alert alert-warning">Warning alert</div>
                        <div class="alert alert-info">Info alert</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-success text-white">
                        <h3 class="card-title">Forms</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" placeholder="Enter your name">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" placeholder="Enter your email">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Message</label>
                            <textarea class="form-control" rows="3" placeholder="Enter your message"></textarea>
                        </div>
                        <button class="btn btn-primary">Submit</button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-4">
                <div class="card shadow">
                    <div class="card-body text-center">
                        <h4 class="text-primary">Layout</h4>
                        <p>Responsive grid system with 12 columns</p>
                        <div class="bg-light p-2 rounded">
                            <div class="bg-primary text-white p-1 m-1">Col 1</div>
                            <div class="bg-secondary text-white p-1 m-1">Col 2</div>
                            <div class="bg-success text-white p-1 m-1">Col 3</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-4">
                <div class="card shadow">
                    <div class="card-body text-center">
                        <h4 class="text-primary">Typography</h4>
                        <p class="text-islamic">Arabic/Islamic fonts supported</p>
                        <p class="text-arabic">النص العربي</p>
                        <h1>H1</h1>
                        <h2>H2</h2>
                        <h3>H3</h3>
                    </div>
                </div>
            </div>
            
            <div class="col-4">
                <div class="card shadow">
                    <div class="card-body text-center">
                        <h4 class="text-primary">Utilities</h4>
                        <p>Spacing, colors, borders, shadows</p>
                        <div class="border border-primary rounded p-2 m-2">
                            Border Primary
                        </div>
                        <div class="shadow p-2 m-2">
                            Shadow
                        </div>
                        <div class="pattern-islamic p-2 m-2">
                            Islamic Pattern
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-info text-white">
                        <h3 class="card-title">Responsive Design</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-md-6 col-lg-3">
                                <div class="bg-primary text-white p-3 rounded text-center">
                                    <h5>Mobile First</h5>
                                    <p class="d-sm-none">Hidden on small screens</p>
                                    <p class="d-none d-sm-block">Visible on small screens and up</p>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-3">
                                <div class="bg-secondary text-white p-3 rounded text-center">
                                    <h5>Flexbox</h5>
                                    <div class="d-flex justify-center align-center">
                                        <span class="m-1">Flex</span>
                                        <span class="m-1">Box</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-3">
                                <div class="bg-success text-white p-3 rounded text-center">
                                    <h5>Colors</h5>
                                    <p>CSS Variables for theming</p>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-3">
                                <div class="bg-warning text-dark p-3 rounded text-center">
                                    <h5>Lightweight</h5>
                                    <p>Pure CSS, no JavaScript required</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="bg-dark text-white p-4 mt-5">
        <div class="container text-center">
            <h4>Safa CSS Framework - صفاء</h4>
            <p>A lightweight, pure CSS framework for IslamWiki</p>
            <p class="text-islamic">إطار عمل CSS خفيف ونقي لـ IslamWiki</p>
        </div>
    </div>
</body>
</html> 