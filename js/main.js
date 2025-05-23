document.addEventListener("DOMContentLoaded", function () {
	// Chat elements
	const chatContainer = document.querySelector(".ai-chat-container");
	const chatMessages = document.getElementById("ai-chat-messages");
	const userInput = document.getElementById("ai-user-input");
	const sendButton = document.getElementById("ai-send-message");
	const minimizeButton = document.querySelector(".ai-chat-minimize");
	const minimizedChat = document.querySelector(".ai-chat-minimized");

	// Store conversation history
	let conversationHistory = [];

	// Set focus on input when chat is opened
	setTimeout(() => {
		userInput.focus();
	}, 500);

	// Auto-resize textarea as user types
	userInput.addEventListener("input", function () {
		this.style.height = "auto";
		this.style.height = Math.min(this.scrollHeight, 140) + "px";

		if (this.scrollHeight > 140) {
			this.style.overflowY = "auto";
		} else {
			this.style.overflowY = "hidden";
		}
	});

	// Handle send message on button click
	sendButton.addEventListener("click", function () {
		sendMessage();
	});

	// Handle send message on Enter key (but allow Shift+Enter for new line)
	userInput.addEventListener("keydown", function (e) {
		if (e.key === "Enter" && !e.shiftKey) {
			e.preventDefault();
			sendMessage();
		}
	});

	// Handle chat minimize/maximize
	minimizeButton.addEventListener("click", function () {
		chatContainer.classList.add("minimized");
		minimizedChat.style.display = "flex";
	});

	minimizedChat.addEventListener("click", function () {
		chatContainer.classList.remove("minimized");
		minimizedChat.style.display = "none";

		// Set focus on input when chat is opened
		setTimeout(() => {
			userInput.focus();
		}, 100);
	});

	// Send message function
	function sendMessage() {
		const message = userInput.value.trim();
		if (!message) return;

		// Add user message to chat and history
		addMessageToChat("user", message);
		conversationHistory.push({ role: "user", message: message });

		// Clear input and reset height
		userInput.value = "";
		userInput.style.height = "auto";
		userInput.focus();

		// Add loading indicator
		const loadingId = addLoadingIndicator();

		// Send request to server
		fetch(aiAnswerGen.ajax_url, {
			method: "POST",
			headers: {
				"Content-Type": "application/x-www-form-urlencoded",
			},
			body: `action=ai_answer_generator&security=${
				aiAnswerGen.nonce
			}&question=${encodeURIComponent(message)}`,
		})
			.then((response) => response.json())
			.then((data) => {
				// Remove loading indicator
				removeLoadingIndicator(loadingId);

				if (data.success) {
					// Format and add AI response to chat and history
					const formattedAnswer = formatContent(data.data.answer);
					addMessageToChat("bot", formattedAnswer, data.data.timestamp);
					conversationHistory.push({ role: "bot", message: data.data.answer });

					// Initialize code highlighting
					initializeCodeHighlighting();
				} else {
					// Add error message
					addErrorMessage(
						data.data.message ||
							"An error occurred while processing your request."
					);
				}

				// Scroll to bottom
				scrollToBottom();
			})
			.catch((error) => {
				// Remove loading indicator
				removeLoadingIndicator(loadingId);

				// Add error message
				addErrorMessage("Network error. Please try again.");

				console.error("Error:", error);
			});

		// Scroll to bottom
		scrollToBottom();
	}

	// Format content for display
	function formatContent(text) {
		// First process code blocks with syntax highlighting
		let processedText = processCodeBlocks(text);

		// Then handle line breaks and paragraphs
		processedText = processLineBreaks(processedText);

		return processedText;
	}

	// Process line breaks and paragraphs
	function processLineBreaks(text) {
		// Replace double line breaks with paragraph tags
		let processed = text.replace(/\n\s*\n/g, "</p><p>");

		// Replace single line breaks with <br> tags
		processed = processed.replace(/\n/g, "<br>");

		// Wrap in paragraph tags if not already wrapped
		if (!processed.startsWith("<p>")) {
			processed = "<p>" + processed;
		}
		if (!processed.endsWith("</p>")) {
			processed = processed + "</p>";
		}

		return processed;
	}

	// Process code blocks
	function processCodeBlocks(text) {
		// Process code blocks with triple backticks
		let processedText = text.replace(
			/```(\w*)([\s\S]*?)```/g,
			function (match, language, code) {
				language = language.trim() || "plaintext";

				return `<div class="code-block">
                <div class="code-header">
                    <span class="code-language">${language}</span>
                    <button class="copy-code-btn" onclick="copyToClipboard(this)">
                        <i class="fas fa-copy"></i> Copy
                    </button>
                </div>
                <pre><code class="language-${language}">${escapeHtml(
					code.trim()
				)}</code></pre>
            </div>`;
			}
		);

		// Process inline code
		processedText = processedText.replace(/`([^`]+)`/g, "<code>$1</code>");

		return processedText;
	}

	// Initialize code highlighting
	function initializeCodeHighlighting() {
		if (window.hljs) {
			document.querySelectorAll(".code-block pre code").forEach((block) => {
				hljs.highlightElement(block);
			});
		}
	}

	// Escape HTML to prevent XSS
	function escapeHtml(unsafe) {
		return unsafe
			.replace(/&/g, "&amp;")
			.replace(/</g, "&lt;")
			.replace(/>/g, "&gt;")
			.replace(/"/g, "&quot;")
			.replace(/'/g, "&#039;");
	}

	// Add message to chat
	function addMessageToChat(sender, content, timestamp = null) {
		if (!timestamp) {
			const now = new Date();
			timestamp = now.toLocaleTimeString([], {
				hour: "numeric",
				minute: "2-digit",
			});
		}

		const messageElement = document.createElement("div");
		messageElement.className = `ai-message ai-message-${sender}`;

		let avatarIcon = sender === "user" ? "fa-user" : "fa-robot";

		// Create message structure
		messageElement.innerHTML = `
            <div class="ai-message-inner">
                <div class="ai-message-avatar">
                    <i class="fas ${avatarIcon}"></i>
                </div>
                <div class="ai-message-content">
                    <div class="ai-content">${content}</div>
                </div>
            </div>
            <div class="ai-message-footer">
                <span class="ai-message-time">${timestamp}</span>
                ${
									sender === "bot"
										? `
                <div class="ai-message-actions">
                    <button class="ai-btn" onclick="copyEntireMessage(this)">
                        <i class="fas fa-copy"></i> Copy
                    </button>
                    <button class="ai-btn ai-btn-regenerate" onclick="regenerateResponse()">
                        <i class="fas fa-sync-alt"></i> Regenerate
                    </button>
                </div>
                `
										: ""
								}
            </div>
        `;

		chatMessages.appendChild(messageElement);
		initializeCodeHighlighting();
		scrollToBottom();
	}

	// Add loading indicator
	function addLoadingIndicator() {
		const loadingId = "loading-" + Date.now();
		const loadingElement = document.createElement("div");
		loadingElement.className = "ai-message ai-message-bot ai-message-loading";
		loadingElement.id = loadingId;

		loadingElement.innerHTML = `
            <div class="ai-message-inner">
                <div class="ai-message-avatar">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="ai-message-content">
                    <div class="ai-typing-indicator">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
            </div>
        `;

		chatMessages.appendChild(loadingElement);
		scrollToBottom();

		return loadingId;
	}

	// Remove loading indicator
	function removeLoadingIndicator(loadingId) {
		const loadingElement = document.getElementById(loadingId);
		if (loadingElement) {
			loadingElement.remove();
		}
	}

	// Add error message
	function addErrorMessage(message) {
		const errorElement = document.createElement("div");
		errorElement.className = "ai-message ai-message-error";

		errorElement.innerHTML = `
            <div class="ai-message-inner">
                <div class="ai-message-avatar">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="ai-message-content">
                    <div class="ai-content">${message}</div>
                </div>
            </div>
            <div class="ai-message-footer">
                <span class="ai-message-time">${new Date().toLocaleTimeString(
									[],
									{ hour: "numeric", minute: "2-digit" }
								)}</span>
            </div>
        `;

		chatMessages.appendChild(errorElement);
		scrollToBottom();
	}

	// Scroll to bottom of chat
	function scrollToBottom() {
		chatMessages.scrollTop = chatMessages.scrollHeight;
	}

	// Regenerate last response
	window.regenerateResponse = function () {
		// Check if there's a conversation to regenerate
		if (conversationHistory.length < 1) return;

		// Find the last user message in history
		let lastUserMessageIndex = -1;
		for (let i = conversationHistory.length - 1; i >= 0; i--) {
			if (conversationHistory[i].role === "user") {
				lastUserMessageIndex = i;
				break;
			}
		}

		if (lastUserMessageIndex === -1) return;

		const lastUserMessage = conversationHistory[lastUserMessageIndex].message;

		// Remove the last bot message from UI and history
		if (conversationHistory[conversationHistory.length - 1].role === "bot") {
			const lastBotMessage = document.querySelector(
				".ai-message-bot:last-of-type"
			);
			if (lastBotMessage) {
				lastBotMessage.remove();
				conversationHistory.pop();
			}
		}

		// Add loading indicator
		const loadingId = addLoadingIndicator();

		// Send request to server for regeneration
		fetch(aiAnswerGen.ajax_url, {
			method: "POST",
			headers: {
				"Content-Type": "application/x-www-form-urlencoded",
			},
			body: `action=ai_answer_generator&security=${
				aiAnswerGen.nonce
			}&question=${encodeURIComponent(lastUserMessage)}&regenerate=true`,
		})
			.then((response) => response.json())
			.then((data) => {
				// Remove loading indicator
				removeLoadingIndicator(loadingId);

				if (data.success) {
					// Format and add AI response to chat and history
					const formattedAnswer = formatContent(data.data.answer);
					addMessageToChat("bot", formattedAnswer, data.data.timestamp);
					conversationHistory.push({ role: "bot", message: data.data.answer });

					// Initialize code highlighting
					initializeCodeHighlighting();
				} else {
					// Add error message
					addErrorMessage(
						data.data.message ||
							"An error occurred while regenerating the response."
					);
				}

				// Scroll to bottom
				scrollToBottom();
			})
			.catch((error) => {
				// Remove loading indicator
				removeLoadingIndicator(loadingId);

				// Add error message
				addErrorMessage("Network error. Please try again.");

				console.error("Error:", error);
			});
	};

	// Copy code to clipboard
	window.copyToClipboard = function (button) {
		const codeBlock = button.closest(".code-block").querySelector("code");
		const text = codeBlock.textContent;

		navigator.clipboard
			.writeText(text)
			.then(() => {
				// Change button text temporarily
				const originalText = button.innerHTML;
				button.innerHTML = '<i class="fas fa-check"></i> Copied!';
				setTimeout(() => {
					button.innerHTML = originalText;
				}, 2000);
			})
			.catch((err) => {
				console.error("Failed to copy text: ", err);

				// Fallback copy method
				fallbackCopyTextToClipboard(text, button);
			});
	};

	// Copy entire message
	window.copyEntireMessage = function (button) {
		const messageElement = button.closest(".ai-message");
		const contentElement = messageElement.querySelector(".ai-content");

		// Get text content minus any styling
		const tempDiv = document.createElement("div");
		tempDiv.innerHTML = contentElement.innerHTML;

		// Remove copy buttons from the clone
		tempDiv.querySelectorAll(".copy-code-btn").forEach((btn) => btn.remove());

		// Get plain text
		const text = tempDiv.textContent.trim();

		navigator.clipboard
			.writeText(text)
			.then(() => {
				// Change button text temporarily
				const originalText = button.innerHTML;
				button.innerHTML = '<i class="fas fa-check"></i> Copied!';
				setTimeout(() => {
					button.innerHTML = originalText;
				}, 2000);
			})
			.catch((err) => {
				console.error("Failed to copy text: ", err);

				// Fallback copy method
				fallbackCopyTextToClipboard(text, button);
			});
	};

	// Fallback method for older browsers
	function fallbackCopyTextToClipboard(text, button) {
		const textArea = document.createElement("textarea");
		textArea.value = text;

		// Make the textarea out of viewport
		textArea.style.position = "fixed";
		textArea.style.left = "-999999px";
		textArea.style.top = "-999999px";
		document.body.appendChild(textArea);
		textArea.focus();
		textArea.select();

		try {
			const successful = document.execCommand("copy");
			if (successful) {
				// Update button text
				const originalText = button.innerHTML;
				button.innerHTML = '<i class="fas fa-check"></i> Copied!';
				setTimeout(() => {
					button.innerHTML = originalText;
				}, 2000);
			}
		} catch (err) {
			console.error("Fallback: Could not copy text: ", err);
		}

		document.body.removeChild(textArea);
	}
});
