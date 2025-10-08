AI Chatbot – Multi‑Provider (OpenAI, DeepSeek, Gemini, Claude)
This app is now a clean OOP PHP project with a provider-based architecture. The UI has been redesigned to resemble ChatGPT with a sidebar, center canvas, and a bottom composer. Users can select the provider and model at runtime.

🚀 Features
Real-time chat interface
Multi-provider (OpenAI, DeepSeek, Gemini, Claude)
Runtime provider/model selector
OOP provider adapters
Logs raw responses for debugging
Modern ChatGPT-like UI
🛠️ Technologies Used
HTML: For the front-end UI
CSS: For styling the chat interface
JavaScript: For handling interactions and AJAX calls
PHP: For backend API communication with OpenAI
OpenAI API: For fetching AI-generated responses
🖥️ Installation and Setup
1. Clone the Repository
bash
Copy code
git clone https://github.com/your-username/your-repo-name.git
cd your-repo-name
2. Install Dependencies
No additional dependencies are required beyond PHP and OpenAI API access.
Make sure PHP is installed and configured on your system.

3. Set API Keys (Environment recommended)
Windows (PowerShell):

```
setx OPENAI_API_KEY "sk-..."
setx DEEPSEEK_API_KEY "ds-..."
setx GEMINI_API_KEY "AIza..."
setx ANTHROPIC_API_KEY "anthropic-..."
```

Or edit placeholders in `api/router.php`.
4. Run the Chatbot Locally
Start a local PHP server:
bash
Copy code
php -S localhost:8000
Open your browser and visit:
http://localhost:8000/index.html
🔧 File Structure

```
/chatbot
├── api/
│   ├── BaseProvider.php
│   ├── router.php
│   └── providers/
│       ├── OpenAI.php
│       ├── Deepseek.php
│       ├── Gemini.php
│       └── Claude.php
├── assets/
│   ├── css/style.css
│   └── js/app.js
├── index.html
├── chat.php
├── debug_log.txt
└── README.md
```
⚙️ Configuration
Model Selection
You can modify the model used in the chat.php file. By default, it uses gpt-3.5-turbo.

php
Copy code
'model' => 'gpt-3.5-turbo',
Adjusting Temperature
Control how creative the responses are by adjusting the temperature parameter in chat.php. Higher values (like 0.9) make output more random, while lower values (like 0.2) make it more focused and deterministic.

php
Copy code
'temperature' => 0.7,
📋 Usage
Choose a provider and model using the dropdowns in the header. Type a message and press Enter or click Send. Use New chat to clear the thread.
🛠️ Troubleshooting
Error: "You exceeded your current quota"
This error indicates your OpenAI free credits have been used up, or your API usage has exceeded the quota.
Solution: Add a billing method at OpenAI Billing to continue using the API.

Empty Message Handling:
The chatbot will warn if the input field is left empty.

API Debug Logs:
If the chatbot fails, check the debug_log.txt for the raw API response to diagnose the issue.

📄 License
This project is licensed under the MIT License. See the LICENSE file for more details.

📧 Contact
For any issues or feedback, feel free to contact:

Dexter (Repository Owner)

🎉 Acknowledgements
OpenAI for providing the API
Bootstrap for styling
Inspired by ChatGPT!
