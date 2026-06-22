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
**Location:** `storage/app/eip/reports/`

### 2. AI Context Report
The compressed, token-safe data sent to the AI.
**Location:** `storage/app/eip/context/`

### 3. AI Final Report
The beautiful, AI-generated summary full of engineering insights and recommendations.
**Location:** `storage/app/eip/ai/`

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