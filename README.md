# Engineering Intelligence Package (EIP)

## What is EIP?
Think of the Engineering Intelligence Package (EIP) as an automated Senior Developer for your Laravel project. It's a smart code analysis tool that catches bugs, security flaws, and messy code before they become a headache.

EIP scans your entire Laravel application, spots common engineering mistakes, and gives you an easy-to-read report packed with actionable advice on how to fix them.

## What Does EIP Do?
EIP dives deep into your Laravel project, looking for architectural flaws, security risks, and performance bottlenecks across all your files—not just controllers, but models, services, routes, jobs, events, and more.

Once the scan is done, EIP takes all those issues, neatly organizes them, and compresses them into a highly optimized format. It then sends this data to an AI engine, which acts as your personal engineering consultant. 

The AI reads the data and hands you back a smart, human-readable report covering:
* **Overall Project Health**: A clear grade on how your codebase is doing.
* **Risk Hotspots**: Exactly which files are causing the most trouble.
* **Security & Architecture Alerts**: Highlighting dangerous patterns like raw SQL queries or overgrown classes.
* **Actionable Advice**: Simple, clear steps you can take to clean things up right now.

Instead of just dumping a massive, confusing list of errors on your screen, EIP gives you the big picture and tells you exactly what matters most.

## Main Features
* **Comprehensive Project Scanning**: Scans everything from Controllers and Models to Jobs and Routes.
* **Deep Security Checks**: Spots vulnerabilities like SQL injections and exposed environment variables.
* **Smart Architecture Insights**: Finds "God Classes" and spaghetti dependencies.
* **AI-Powered Engineering Analysis**: Get feedback from top-tier AI models.
* **Token-Safe AI Pipeline**: Intelligently compresses data so you don't blow through your API limits.
* **Terminal & Markdown Reports**: See your results instantly in the console, or save them for later.

## How EIP Works
EIP follows a simple 3-step process:

### 1. The Deep Scan
EIP crawls through your entire Laravel application, automatically identifying different file types and running specialized rules against them to find coding issues and security risks.

### 2. Context Engineering
All the issues EIP finds are gathered up, grouped by file, and squashed into a highly compressed "context payload." This ensures the AI gets all the facts without wasting expensive tokens.

### 3. The AI Review
The AI engine takes that optimized context, reviews the structural health of your app, and generates a clear, helpful report telling you exactly how to improve your code.

## What rules does it check?
EIP comes loaded with dozens of checks out of the box. Here are just a few of the things it looks for:

**Architecture & Clean Code**
- **Fat Controllers & God Services**: Detects classes that have grown too large and are trying to do too many things at once.
- **Long Methods**: Finds massively long functions that are a nightmare to test and maintain.
- **Too Many Dependencies**: Warns you when a class is relying on way too many external services.

**Security & Performance**
- **Raw SQL Injections**: Scans your database queries for raw SQL strings that could leave you wide open to attacks.
- **Exposed Env Variables**: Checks if you are using the `env()` helper outside of your config files, which can break your app in production.
- **Mass Assignment Vulnerabilities**: Ensures your Models properly lock down their database columns using `$fillable` or `$guarded`.
- **Potential N+1 Queries**: Spots database calls hiding inside loops, which silently kill your application's speed.
- **Uncacheable Routes**: Detects Closure-based routes that prevent Laravel from optimizing your app's loading time.
- **Synchronous Jobs**: Flags jobs that aren't queued, which will force your users to wait while heavy background tasks finish.

## Generated Reports
EIP saves different types of reports depending on what you need. You can find them all neatly organized in your storage folder:

### 1. Raw Report
The complete, unedited list of every single issue found. Great for debugging.
**Location:** `storage/app/eip/reports/eip-raw-{timestamp}.json`

### 2. AI Context
The compressed, token-safe data sent to the AI. *(Note: This is generated in-memory for the AI and is not saved to your disk to keep your project clean).*

### 3. AI Final Report (if AI is enabled)
The beautiful, AI-generated summary full of engineering insights and recommendations.
**Location:** `storage/app/eip/ai/eip-ai-report-{timestamp}.json`

*When you run the command, EIP will print absolute, clickable links to these files directly in your terminal!*

## Supported AI Providers
EIP lets you choose which AI brain you want to use. We currently support:
* **OpenAI** (GPT-4, etc.)
* **Gemini** (Google's latest models)
* **Mistral** (Codestral, etc.)
* **OpenRouter** (Access to dozens of open-source models)

## Example AI Report Output
```json
{
  "health_score": 74,
  "critical_issues": 2,
  "hotspots_detected": 5,
  "recommendations": [
    "Move business logic out of the UserService and into dedicated action classes.",
    "Fix potential SQL Injection vulnerabilities in the InvitationService.",
    "Cache your routes by moving Closure logic to Controllers."
  ]
}
```

# Usage

## Publish the Configuration File

After installing the package, publish the EIP configuration file:

```bash
php artisan vendor:publish --tag=eip-config
```

This command will create the EIP configuration file inside your application's `config` directory.

---

## Enable AI-Powered Insights (Optional)

EIP can generate AI-powered engineering insights and recommendations.

To enable AI analysis, add the following settings to your `.env` file:

```env
EIP_AI_ENABLED=true
EIP_AI_PROVIDER=openai
```

### Supported AI Providers

```env
openai
gemini
openrouter
mistral
```

### Unified Environment Variables

Regardless of which AI provider you choose, you use the same environment variables to configure it:

```env
EIP_AI_ENABLED=true
EIP_AI_PROVIDER=gemini        # openai, gemini, openrouter, or mistral
EIP_AI_MODEL=gemini-2.5-pro   # e.g., gpt-4o, codestral-latest
EIP_AI_KEY=your-api-key-here
```

> AI integration is optional. EIP can generate engineering reports even when AI is disabled.

---

# Running Analysis

## Standard Analysis

Generate a summarized engineering report in the console, along with the raw JSON file (and AI JSON file, if enabled):

```bash
php artisan eip
```

## Markdown Report

Generate a Markdown report:

```bash
php artisan eip --markdown
```

## Generate All Reports

Generate both JSON and Markdown reports:

```bash
php artisan eip --export
```

---

# Available Options

Here is a quick summary of all available command-line flags:

| Option | Description |
| :--- | :--- |
| `--markdown` | Output report in Markdown format |
| `--export` | Generate all reports (JSON + Markdown) |
| `--output=` | Custom output directory or file path |
| `--severity=` | Filter output by severity (`critical`, `high`, `warning`, `info`) |
| `--type=` | Filter output by issue type slug (e.g. `potential_n_plus_one`) |
| `--file=` | Partial filename filter for output (e.g. `UserController`) |
| `--limit=` | Cap output to N issues |
| `--sort=` | Sort field: `severity` (default), `file`, `line` |

### Save Reports to a Custom Location

```bash
php artisan eip --output=storage/reports
```

### Filter by Severity

Show only issues of a specific severity level:

```bash
php artisan eip --severity=critical
```

Available severity levels:

```text
critical
high
warning
info
```

### Filter by Issue Type

Show only specific issue types:

```bash
php artisan eip --type=potential_n_plus_one
```

Available issue types:

```text
closure_route_detected
env_helper_outside_config
event_contains_handler
fat_controller
god_class_service
long_method
mass_assignment_vulnerability
missing_form_request
missing_transaction
potential_n_plus_one
potential_sql_injection
synchronous_job_dispatch
too_many_dependencies
```

### Filter by File Name

Show issues related to a specific file:

```bash
php artisan eip --file=UserController
```

### Limit Console Output

Display only a specific number of issues:

```bash
php artisan eip --limit=20
```

### Sort Results

Sort output by severity, file name, or line number:

```bash
php artisan eip --sort=severity
```

Available sort options:

```text
severity
file
line
```

---

# Example Commands

```bash
# Generate a standard report (with auto-generated JSON files)
php artisan eip

# Generate a Markdown report
php artisan eip --markdown

# Generate all report formats
php artisan eip --export

# Show only critical issues
php artisan eip --severity=critical

# Show issues from UserController
php artisan eip --file=UserController

# Show only N+1 query issues
php artisan eip --type=potential_n_plus_one

# Limit output to 10 results
php artisan eip --limit=10
```