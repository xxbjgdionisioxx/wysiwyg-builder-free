<!-- components/properties.php -->
<aside class="sidebar sidebar--right">
    <div class="sidebar__tabs">
        <button class="tab-btn active" data-target="panel-properties">Properties</button>
        <button class="tab-btn" data-target="panel-database">Database</button>
    </div>
    
    <div class="sidebar__content">
        <!-- Properties Panel -->
        <div id="panel-properties" class="tab-content active">
            <div id="no-selection" class="empty-state">
                <i class="fa-solid fa-hand-pointer"></i>
                <p>Select an element on the canvas to edit its properties.</p>
            </div>
            
            <div id="properties-form" style="display: none;">
                <div class="prop-group">
                    <label>Element Type</label>
                    <div id="prop-type" class="prop-value-readonly">Text</div>
                </div>

                <div class="prop-group">
                    <label>ID</label>
                    <input type="text" id="prop-id" placeholder="e.g. hero-title">
                </div>

                <div class="prop-group">
                    <label>Classes</label>
                    <input type="text" id="prop-classes" placeholder="e.g. text-center mb-4">
                </div>

                <div class="prop-category">Content</div>
                <div class="prop-group" id="group-text">
                    <label>Text Content</label>
                    <textarea id="prop-text" rows="3"></textarea>
                </div>
                <div class="prop-group" id="group-src" style="display: none;">
                    <label>Image Source</label>
                    <input type="text" id="prop-src" placeholder="https://...">
                </div>

                <div class="prop-category">Typography</div>
                <div class="prop-row">
                    <div class="prop-group">
                        <label>Size</label>
                        <input type="text" id="style-fontSize" placeholder="e.g. 16px">
                    </div>
                    <div class="prop-group">
                        <label>Weight</label>
                        <select id="style-fontWeight">
                            <option value="">Default</option>
                            <option value="400">Normal</option>
                            <option value="500">Medium</option>
                            <option value="600">Semi Bold</option>
                            <option value="700">Bold</option>
                        </select>
                    </div>
                </div>
                <div class="prop-group">
                    <label>Color</label>
                    <input type="color" id="style-color">
                </div>
                <div class="prop-group">
                    <label>Align</label>
                    <div class="btn-group">
                        <button class="btn-icon" data-style="textAlign" data-val="left"><i class="fa-solid fa-align-left"></i></button>
                        <button class="btn-icon" data-style="textAlign" data-val="center"><i class="fa-solid fa-align-center"></i></button>
                        <button class="btn-icon" data-style="textAlign" data-val="right"><i class="fa-solid fa-align-right"></i></button>
                    </div>
                </div>

                <div class="prop-category">Spacing</div>
                <div class="prop-row">
                    <div class="prop-group">
                        <label>Margin</label>
                        <input type="text" id="style-margin" placeholder="0px">
                    </div>
                    <div class="prop-group">
                        <label>Padding</label>
                        <input type="text" id="style-padding" placeholder="0px">
                    </div>
                </div>

                <div class="prop-category">Appearance</div>
                <div class="prop-group">
                    <label>Background</label>
                    <input type="color" id="style-backgroundColor">
                </div>
                <div class="prop-row">
                    <div class="prop-group">
                        <label>Width</label>
                        <input type="text" id="style-width" placeholder="auto">
                    </div>
                    <div class="prop-group">
                        <label>Height</label>
                        <input type="text" id="style-height" placeholder="auto">
                    </div>
                </div>
                
                <div class="prop-category" id="group-data-binding" style="display: none;">
                    <label>Data Binding</label>
                    <div class="binding-info">
                        <span id="binding-path" class="badge">None</span>
                        <button id="btn-remove-binding" class="btn-icon"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                    <small>Drag a column from the Database tab here to bind.</small>
                    <div id="binding-dropzone" class="dropzone">Drop column here</div>
                </div>
            </div>
        </div>
        
        <!-- Database Panel -->
        <div id="panel-database" class="tab-content">
            <div class="db-actions">
                <button id="btn-refresh-db" class="btn btn--sm"><i class="fa-solid fa-rotate"></i> Refresh</button>
                <button id="btn-generate-schema" class="btn btn--sm btn--primary"><i class="fa-solid fa-magic"></i> Auto-Schema</button>
            </div>
            <div id="db-tree" class="db-tree">
                <!-- Loaded via JS -->
                <div class="empty-state">Loading schema...</div>
            </div>
        </div>
    </div>
</aside>
