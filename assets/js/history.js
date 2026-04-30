// assets/js/history.js
import { getCanvasState, setCanvasState } from './canvas.js';

let historyStack = [];
let currentIndex = -1;
const MAX_HISTORY = 20;

export function initHistory() {
    // Save initial state
    saveState();

    // Listen for state changes
    window.addEventListener('builder-state-changed', () => {
        saveState();
    });

    // Undo/Redo Buttons
    document.getElementById('btn-undo').addEventListener('click', undo);
    document.getElementById('btn-redo').addEventListener('click', redo);

    // Keyboard Shortcuts
    document.addEventListener('keydown', (e) => {
        if (e.ctrlKey || e.metaKey) {
            if (e.key === 'z') {
                e.preventDefault();
                undo();
            } else if (e.key === 'y') {
                e.preventDefault();
                redo();
            }
        }
    });
}

function saveState() {
    const currentState = getCanvasState();
    
    // Don't save if state hasn't changed
    if (currentIndex >= 0 && historyStack[currentIndex] === currentState) {
        return;
    }

    // If we're not at the end of the stack and a new action occurs,
    // discard future states (redo history)
    if (currentIndex < historyStack.length - 1) {
        historyStack = historyStack.slice(0, currentIndex + 1);
    }

    historyStack.push(currentState);
    
    if (historyStack.length > MAX_HISTORY) {
        historyStack.shift();
    } else {
        currentIndex++;
    }
}

export function undo() {
    if (currentIndex > 0) {
        currentIndex--;
        setCanvasState(historyStack[currentIndex]);
    }
}

export function redo() {
    if (currentIndex < historyStack.length - 1) {
        currentIndex++;
        setCanvasState(historyStack[currentIndex]);
    }
}
