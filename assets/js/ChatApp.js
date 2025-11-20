/**
 * Main Chat Application Class - OOP JavaScript
 */
class ChatApp {
    constructor() {
        this.apiBase = 'api/router.php';
        this.currentSessionId = null;
        this.conversation = [];
        this.providers = [];
        this.models = [];
        
        this.initializeElements();
        this.initializeEventListeners();
        this.loadProviders();
    }

    initializeElements() {
        this.elements = {
            messages: document.getElementById('chat-messages'),
            input: document.getElementById('user-input'),
            sendBtn: document.getElementById('send-btn'),
            newChatBtn: document.getElementById('new-chat'),
            providerSelect: document.getElementById('provider-select'),
            modelSelect: document.getElementById('model-select'),
            sidebar: document.querySelector('.sidebar'),
            menuBtn: document.getElementById('menu-btn'),
            sessionsList: document.getElementById('sessions-list'),
            settingsBtn: document.getElementById('settings-btn'),
            analyticsBtn: document.getElementById('analytics-btn'),
        };
    }

    initializeEventListeners() {
        this.elements.sendBtn.addEventListener('click', () => this.sendMessage());
        this.elements.input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.sendMessage();
            }
        });
        
        this.elements.newChatBtn.addEventListener('click', () => this.createNewChat());
        this.elements.providerSelect.addEventListener('change', () => this.loadModels());
        
        if (this.elements.menuBtn) {
            this.elements.menuBtn.addEventListener('click', () => {
                this.elements.sidebar.classList.toggle('open');
            });
        }

        if (this.elements.settingsBtn) {
            this.elements.settingsBtn.addEventListener('click', () => this.showSettings());
        }

        if (this.elements.analyticsBtn) {
            this.elements.analyticsBtn.addEventListener('click', () => this.showAnalytics());
        }
    }

    async loadProviders() {
        try {
            const response = await fetch(this.apiBase);
            const data = await response.json();
            
            if (data.ok && data.providers) {
                this.providers = data.providers;
                this.populateProviders();
                this.loadModels();
            }
        } catch (error) {
            console.error('Failed to load providers:', error);
        }
    }

    populateProviders() {
        this.elements.providerSelect.innerHTML = '';
        this.providers.forEach(provider => {
            if (!provider.models || provider.models.length === 0) return;
            
            const option = document.createElement('option');
            option.value = provider.provider;
            option.textContent = provider.name || provider.provider;
            this.elements.providerSelect.appendChild(option);
        });
    }

    async loadModels() {
        const providerKey = this.elements.providerSelect.value;
        const provider = this.providers.find(p => p.provider === providerKey);
        
        if (!provider) return;

        this.models = provider.models || [];
        this.elements.modelSelect.innerHTML = '';
        
        this.models.forEach(model => {
            const option = document.createElement('option');
            option.value = model;
            option.textContent = model;
            this.elements.modelSelect.appendChild(option);
        });
    }

    async sendMessage() {
        const text = this.elements.input.value.trim();
        if (!text) return;

        this.addMessage('user', text);
        this.elements.input.value = '';
        this.setComposerDisabled(true);

        const messages = [
            ...this.conversation,
            { role: 'user', content: text }
        ];

        try {
            const response = await fetch(this.apiBase, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    provider: this.elements.providerSelect.value,
                    model: this.elements.modelSelect.value,
                    messages: messages,
                    session_id: this.currentSessionId,
                    temperature: this.getTemperature()
                })
            });

            const data = await response.json();
            
            if (data.ok && data.reply) {
                this.addMessage('assistant', data.reply);
                this.conversation.push({ role: 'user', content: text });
                this.conversation.push({ role: 'assistant', content: data.reply });
            } else {
                this.addMessage('assistant', data.error || 'Sorry, something went wrong.');
            }
        } catch (error) {
            console.error('Error:', error);
            this.addMessage('assistant', 'Sorry, something went wrong.');
        } finally {
            this.setComposerDisabled(false);
        }
    }

    addMessage(role, text) {
        const div = document.createElement('div');
        div.className = `message ${role}`;
        
        const content = document.createElement('div');
        content.className = 'message-content';
        content.textContent = text;
        
        div.appendChild(content);
        this.elements.messages.appendChild(div);
        this.elements.messages.scrollTop = this.elements.messages.scrollHeight;
    }

    async createNewChat() {
        try {
            const response = await fetch(this.apiBase, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    path: 'session',
                    action: 'create',
                    title: 'New Chat'
                })
            });

            const data = await response.json();
            if (data.ok && data.session_id) {
                this.currentSessionId = data.session_id;
                this.conversation = [
                    { role: 'system', content: 'You are a helpful assistant.' }
                ];
                this.elements.messages.innerHTML = '';
            }
        } catch (error) {
            console.error('Failed to create session:', error);
        }
    }

    setComposerDisabled(disabled) {
        this.elements.input.disabled = disabled;
        this.elements.sendBtn.disabled = disabled;
    }

    getTemperature() {
        // Could be retrieved from settings
        return 0.7;
    }

    async showSettings() {
        // Settings modal implementation
        console.log('Settings clicked');
    }

    async showAnalytics() {
        try {
            const response = await fetch(`${this.apiBase}?path=analytics&days=7`);
            const data = await response.json();
            
            if (data.ok && data.analytics) {
                console.log('Analytics:', data.analytics);
                // Display analytics in a modal or sidebar
            }
        } catch (error) {
            console.error('Failed to load analytics:', error);
        }
    }
}

// Initialize app when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.chatApp = new ChatApp();
});
