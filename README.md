=== AI Answer Generator ===

Contributors: hariharan-g, boostmyshop

Tags: AI, chatbot, OpenAI, Gemini, GPT, AI assistant, support bot, WordPress AI, Tailwind CSS

Requires at least: 5.5

Tested up to: 6.5

Requires PHP: 7.4

Stable tag: 1.0.0

License: GPLv2 or later

License URI: https://www.gnu.org/licenses/gpl-2.0.html

A powerful AI-powered chatbot plugin for WordPress that leverages Google Gemini (or OpenAI) models to generate intelligent responses to user queries in real-time.

== Description ==

**AI Answer Generator** brings the power of generative AI to your WordPress website. Whether you're running a blog, eCommerce site, or service platform, this plugin acts as a virtual assistant, providing smart, real-time answers to your visitors’ questions.

Built using modern frontend tools like Tailwind CSS and integrated with Google's Gemini models (or OpenAI-compatible APIs), the plugin is simple to deploy and highly customizable for your brand.

**Key Features:**
- Conversational AI powered by Google Gemini API.
- Clean and responsive chatbot UI with Tailwind CSS.
- Easy-to-use shortcode to place the chatbot anywhere.
- Welcome message, color theme, and bot name customization.
- Code syntax highlighting (great for developer-focused sites).
- Minimize/expand chat widget interface.
- Lightweight and GDPR-compliant.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/ai-answer-generator` directory, or install the plugin directly through the WordPress plugins screen.
2. Activate the plugin through the ‘Plugins’ screen in WordPress.
3. Go to **Settings > AI Answer Generator** to configure your API key, model, and UI settings.
4. Use the `[ai_answer_generator]` shortcode on any page or post where you want the chatbot to appear.

== Usage ==

You can customize the chatbot with the following shortcode parameters:

`[ai_answer_generator width="100%" max_width="480px" theme="light"]`

- `width`: Set the full width of the widget container.
- `max_width`: Limit the maximum width (recommended: 480px).
- `theme`: Choose a color theme (light/dark).

== Frequently Asked Questions ==

= What model does this plugin use? =

Currently, it uses the **Gemini 1.5 Flash** model from Google via their Generative Language API. You can configure the model in the plugin settings.

= Is OpenAI supported? =

Out of the box, this version uses Gemini API. You can extend it to support OpenAI by modifying the request endpoint and payload format.

= Can I disable branding? =

Yes, you can turn off the "Powered by" message in the settings.

== Screenshots ==

1. AI Chat Interface with welcome message and real-time messaging.
2. Plugin Settings Page in WP Admin.
3. Chatbot embedded in a post using shortcode.

== Changelog ==

= 1.0.0 =
* Initial release
* Chat UI with Tailwind CSS
* Gemini API integration
* Settings panel for API key and customization
* Shortcode-based embed

== Upgrade Notice ==

No upgrades required for version 1.0.0.

== Credits ==

- Developed by Hariharan G – [BoostMyShop](https://boostmyshop.com/)
- Inspired by OpenAI and Google Gemini APIs
- UI built with TailwindCSS and Font Awesome

== License ==

This plugin is licensed under the GPLv2 or later. See https://www.gnu.org/licenses/gpl-2.0.html for more information.
