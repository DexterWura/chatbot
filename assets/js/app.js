const messagesEl = document.getElementById('chat-messages');
const inputEl = document.getElementById('user-input');
const sendBtn = document.getElementById('send-btn');
const newChatBtn = document.getElementById('new-chat');
const providerSelect = document.getElementById('provider-select');
const modelSelect = document.getElementById('model-select');
const sidebarEl = document.querySelector('.sidebar');
const menuBtn = document.getElementById('menu-btn');

let conversation = [
  { role: 'system', content: 'You are a helpful assistant.' }
];

init();

function init() {
  fetch('api/router.php')
    .then(r => r.json())
    .then(({ providers }) => {
      providerSelect.innerHTML = '';
      providers.forEach(p => {
        const opt = document.createElement('option');
        opt.value = p.provider;
        opt.textContent = p.provider;
        providerSelect.appendChild(opt);
      });
      populateModels();
    })
    .catch(() => {});

  sendBtn.addEventListener('click', onSend);
  inputEl.addEventListener('keydown', e => { if (e.key === 'Enter') onSend(); });
  newChatBtn.addEventListener('click', resetChat);
  providerSelect.addEventListener('change', populateModels);
  if (menuBtn){ menuBtn.addEventListener('click', () => sidebarEl.classList.toggle('open')); }
}

function populateModels() {
  // refetch providers to get models (simple approach)
  fetch('api/router.php')
    .then(r => r.json())
    .then(({ providers }) => {
      const p = providers.find(x => x.provider === providerSelect.value) || providers[0];
      modelSelect.innerHTML = '';
      p.models.forEach(m => {
        const opt = document.createElement('option');
        opt.value = m;
        opt.textContent = m;
        modelSelect.appendChild(opt);
      });
    });
}

function onSend() {
  const text = (inputEl.value || '').trim();
  if (!text) return;
  addMessage('user', text);
  inputEl.value = '';
  setComposerDisabled(true);

  const payload = {
    provider: providerSelect.value,
    model: modelSelect.value,
    messages: [
      ...conversation,
      { role: 'user', content: text }
    ]
  };

  fetch('api/router.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  })
    .then(r => r.json())
    .then(data => {
      const reply = data.reply || 'No response';
      addMessage('assistant', reply);
      conversation.push({ role: 'user', content: text });
      conversation.push({ role: 'assistant', content: reply });
    })
    .catch(err => {
      addMessage('assistant', 'Sorry, something went wrong.');
      console.error(err);
    })
    .finally(() => setComposerDisabled(false));
}

function addMessage(role, text) {
  const div = document.createElement('div');
  div.className = 'message ' + (role === 'user' ? 'user' : 'assistant');
  div.textContent = text;
  messagesEl.appendChild(div);
  messagesEl.scrollTop = messagesEl.scrollHeight;
}

function resetChat() {
  messagesEl.innerHTML = '';
  conversation = [
    { role: 'system', content: 'You are a helpful assistant.' }
  ];
}

function setComposerDisabled(disabled) {
  inputEl.disabled = disabled;
  sendBtn.disabled = disabled;
}


