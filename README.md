AI Chatbot using OpenAI API
This is a simple AI-powered chatbot built using HTML, CSS, JavaScript, and PHP. The chatbot interacts with users and fetches intelligent responses using the OpenAI API (GPT models).

🚀 Features
Real-time chat interface
Supports OpenAI GPT-3.5-Turbo model (configurable)
Handles empty input and API errors gracefully
Logs raw responses for debugging
Easily customizable front-end using HTML/CSS
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

3. Set Up OpenAI API Key
Get your OpenAI API key from OpenAI.
Add your API key to the PHP code in chat.php:
php
Copy code
$apiKey = 'your_openai_api_key';
4. Run the Chatbot Locally
Start a local PHP server:
bash
Copy code
php -S localhost:8000
Open your browser and visit:
http://localhost:8000/index.html
🔧 File Structure
bash
Copy code
/your-repo-name
│
├── index.html        # Front-end chat UI
├── style.css         # CSS for styling the chatbot
├── script.js         # JavaScript for AJAX requests
├── chat.php          # PHP backend to connect with OpenAI API
└── README.md         # Documentation (this file)
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
Enter your message in the chat input field.
Click Send or press Enter to receive a response from the AI.
If the API quota is exceeded, an error message will be displayed. Ensure your OpenAI API credits are available.
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
