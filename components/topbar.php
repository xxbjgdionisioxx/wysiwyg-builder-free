<!-- components/topbar.php -->
<header class="topbar">
    <div class="topbar__left">
        <div class="topbar__logo">
            <i class="fa-solid fa-wand-magic-sparkles"></i> WYSIWYG
        </div>
        <div class="topbar__project-name" contenteditable="true" id="project-name">Untitled Project</div>
    </div>
    
    <div class="topbar__center">
        <button class="topbar__btn" id="btn-desktop" title="Desktop View" class="active"><i class="fa-solid fa-desktop"></i></button>
        <button class="topbar__btn" id="btn-tablet" title="Tablet View"><i class="fa-solid fa-tablet-screen-button"></i></button>
        <button class="topbar__btn" id="btn-mobile" title="Mobile View"><i class="fa-solid fa-mobile-screen-button"></i></button>
        
        <div class="topbar__divider"></div>
        
        <button class="topbar__btn" id="btn-undo" title="Undo (Ctrl+Z)"><i class="fa-solid fa-rotate-left"></i></button>
        <button class="topbar__btn" id="btn-redo" title="Redo (Ctrl+Y)"><i class="fa-solid fa-rotate-right"></i></button>
        
        <div class="topbar__divider"></div>
        
        <button class="topbar__btn toggle-mode active" id="mode-edit" data-mode="edit">Edit</button>
        <button class="topbar__btn toggle-mode" id="mode-preview" data-mode="preview">Preview</button>
        <button class="topbar__btn toggle-mode" id="mode-code" data-mode="code">Code</button>
    </div>
    
    <div class="topbar__right">
        <button class="btn btn--secondary" id="btn-load">Load</button>
        <button class="btn btn--primary" id="btn-save">Save</button>
        <button class="btn btn--success" id="btn-export"><i class="fa-solid fa-download"></i> Export</button>
        <button class="topbar__btn" id="btn-logout" title="Logout"><i class="fa-solid fa-arrow-right-from-bracket"></i></button>
    </div>
</header>
