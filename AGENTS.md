<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4
- inertiajs/inertia-laravel (INERTIA_LARAVEL) - v3
- laravel/fortify (FORTIFY) - v1
- laravel/framework (LARAVEL) - v13
- laravel/prompts (PROMPTS) - v0
- laravel/wayfinder (WAYFINDER) - v0
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12
- @inertiajs/react (INERTIA_REACT) - v3
- react (REACT) - v19
- tailwindcss (TAILWINDCSS) - v4
- @laravel/vite-plugin-wayfinder (WAYFINDER_VITE) - v0
- eslint (ESLINT) - v9
- prettier (PRETTIER) - v3

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== inertia-laravel/core rules ===

# Inertia

- Inertia creates fully client-side rendered SPAs without modern SPA complexity, leveraging existing server-side patterns.
- Components live in `resources/js/pages` (unless specified in `vite.config.js`). Use `Inertia::render()` for server-side routing instead of Blade views.
- ALWAYS use `search-docs` tool for version-specific Inertia documentation and updated code examples.
- IMPORTANT: Activate `inertia-react-development` when working with Inertia client-side patterns.

# Inertia v3

- Use all Inertia features from v1, v2, and v3. Check the documentation before making changes to ensure the correct approach.
- New v3 features: standalone HTTP requests (`useHttp` hook), optimistic updates with automatic rollback, layout props (`useLayoutProps` hook), instant visits, simplified SSR via `@inertiajs/vite` plugin, custom exception handling for error pages.
- Carried over from v2: deferred props, infinite scroll, merging props, polling, prefetching, once props, flash data.
- When using deferred props, add an empty state with a pulsing or animated skeleton.
- Axios has been removed. Use the built-in XHR client with interceptors, or install Axios separately if needed.
- `Inertia::lazy()` / `LazyProp` has been removed. Use `Inertia::optional()` instead.
- Prop types (`Inertia::optional()`, `Inertia::defer()`, `Inertia::merge()`) work inside nested arrays with dot-notation paths.
- SSR works automatically in Vite dev mode with `@inertiajs/vite` - no separate Node.js server needed during development.
- Event renames: `invalid` is now `httpException`, `exception` is now `networkError`.
- `router.cancel()` replaced by `router.cancelAll()`.
- The `future` configuration namespace has been removed - all v2 future options are now always enabled.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== wayfinder/core rules ===

# Laravel Wayfinder

Use Wayfinder to generate TypeScript functions for Laravel routes. Import from `@/actions/` (controllers) or `@/routes/` (named routes).

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `php artisan make:test --pest {name}`.
- The `{name}` argument should not include the test suite directory. Use `php artisan make:test --pest SomeFeatureTest` instead of `php artisan make:test --pest Feature/SomeFeatureTest`.
- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.

=== inertia-react/core rules ===

# Inertia + React

- IMPORTANT: Activate `inertia-react-development` when working with Inertia React client-side patterns.

</laravel-boost-guidelines>

---

## Agent Core Rules

Rules berikut bersifat **non-negotiable** dan harus diikuti di setiap sesi kerja pada proyek ini.

---

## 1. Response Language: Bahasa Indonesia (MANDATORY)

### Strict Rules

- **ALWAYS** respond in Bahasa Indonesia for ALL communications — no exceptions.
- This applies to: explanations, clarification questions, error messages, suggestions, technical discussions, commit message descriptions, debugging explanations, and every other form of communication with the user.
- **NEVER** switch to English or any other language unless the user explicitly requests it.
- If unsure, default to Bahasa Indonesia.

### Exceptions (Remain in English)

The following elements should remain in English as they are part of programming conventions:

- Variable names, function names, class names, and code identifiers.
- Programming syntax and language-specific keywords.
- Library, package, framework, and tool names.
- Git commit messages (optional — follow user preference).
- Technical configuration file contents (package.json, tsconfig.json, etc.).

### Example

```
✅ Correct:
"Saya akan membuat komponen React untuk halaman login. Pertama, mari kita install dependency yang dibutuhkan..."

❌ Wrong:
"I'll create a React component for the login page. First, let's install the required dependencies..."
```

### Code Comments

- All inline code comments MUST be written in **Bahasa Indonesia**.
- JSDoc/TSDoc: descriptions in Bahasa Indonesia, type annotations remain in English.

```typescript
/**
 * Mengambil data pengguna berdasarkan ID.
 * Mengembalikan null jika pengguna tidak ditemukan.
 *
 * @param userId - ID unik pengguna
 * @returns Data pengguna atau null
 */
async function fetchUserData(userId: string): Promise<User | null> {
  // Validasi input sebelum melakukan query ke database
  if (!userId) return null;

  // Ambil data dari database
  const user = await db.users.findUnique({ where: { id: userId } });
  return user;
}
```

### Communication Tone

- Explain every technical decision in Bahasa Indonesia.
- If a breaking change or deprecation is found, inform the user in Bahasa Indonesia with alternative solutions.
- When suggesting architectural changes, provide clear reasoning in Bahasa Indonesia.
- Error messages directed at the user must be in Bahasa Indonesia.
- Internal technical logs may remain in English.

---

## 2. Mandatory Use of Context7 MCP

### Strict Rules

- **ALWAYS** use Context7 MCP to fetch current documentation before writing any code that involves external libraries, frameworks, or tools.
- This applies to **all** of the following situations:
  - Using libraries/frameworks already present in the project.
  - Adding new libraries/frameworks to the project.
  - Upgrading or migrating library versions.
  - Writing code that depends on any external library API.
  - Fixing bugs related to library usage.
  - Configuring build tools, bundlers, or dev tooling.

### How to Use

Append `use context7` to every internal prompt when documentation reference is needed. Context7 will automatically fetch up-to-date, version-specific documentation from official sources.

### Required Workflow

```
1. User requests a feature/change involving the tech stack
2. MANDATORY: Use Context7 to fetch current documentation
3. Verify that APIs, methods, and patterns match the current version
4. Only then write/modify code based on accurate documentation
5. If there is a conflict between internal knowledge and Context7, ALWAYS PRIORITIZE Context7 results
```

### Situations Requiring Context7

| Situation | Context7 Action |
|---|---|
| Creating a new React component | Check React docs for the project's installed version |
| Setting up Next.js routing | Check App Router vs Pages Router for the installed Next.js version |
| Configuring Tailwind CSS | Check config syntax for the current Tailwind version |
| Querying database with Prisma | Check Prisma Client API for the installed version |
| Adding Express middleware | Check current Express middleware patterns |
| Setting up authentication | Check docs for the auth library in use (NextAuth, Clerk, etc.) |
| Third-party API integration | Check the latest SDK/library wrapper version |
| Configuring Vite/Webpack/Turbopack | Check bundler docs for current configuration API |

### Prohibited Actions

```
❌ Writing code based on training data without Context7 verification
❌ Assuming an API or method is still valid without checking current docs
❌ Skipping Context7 because you believe you already "know" a library
❌ Using patterns or syntax that may have been deprecated
```

---

## 3. Context7 MCP Configuration

### Setup via CLI (Recommended)

```bash
claude mcp add context7 -- npx -y @upstash/context7-mcp@latest
```

### Setup via Remote HTTP

```bash
claude mcp add --transport http context7 https://mcp.context7.com/mcp
```

### Manual Setup (claude_desktop_config.json or .mcp.json)

```json
{
  "mcpServers": {
    "context7": {
      "command": "npx",
      "args": ["-y", "@upstash/context7-mcp@latest"]
    }
  }
}
```

### Setup with API Key (Higher Rate Limits)

Get a free API key at [context7.com/dashboard](https://context7.com/dashboard).

```json
{
  "mcpServers": {
    "context7": {
      "command": "npx",
      "args": ["-y", "@upstash/context7-mcp@latest", "--api-key", "YOUR_API_KEY"]
    }
  }
}
```

### Verify Connection

```bash
claude mcp list
```

Ensure the output shows `context7` with `✓ Connected` status.

---

## 4. Mandatory Coding Principles

The following coding principles **MUST** be applied in every piece of code that is written, modified, or reviewed. The goal is to produce software that is maintainable, scalable, readable, and minimizes technical debt for long-term collaboration.

### SOLID Principles (Object-Oriented Design)

- **Single Responsibility (SRP)**: A class or module should have one, and only one, reason to change. If a class handles more than one responsibility, split it into separate classes.

```typescript
// ❌ Wrong: One class handling multiple responsibilities
class UserService {
  createUser() { /* ... */ }
  sendEmail() { /* ... */ }
  generateReport() { /* ... */ }
}

// ✅ Correct: Each class has a single responsibility
class UserService {
  createUser() { /* ... */ }
}
class EmailService {
  sendEmail() { /* ... */ }
}
class ReportService {
  generateReport() { /* ... */ }
}
```

- **Open/Closed (OCP)**: Software entities should be open for extension but closed for modification. Use abstractions so new features can be added without altering existing code.

- **Liskov Substitution (LSP)**: Subtypes must be substitutable for their base types without altering program correctness. Every derived class must fulfill the contract of its parent class.

- **Interface Segregation (ISP)**: Clients should not be forced to depend on methods they do not use. Split large interfaces into smaller, focused ones.

```typescript
// ❌ Wrong: Interface is too broad
interface Worker {
  work(): void;
  eat(): void;
  sleep(): void;
}

// ✅ Correct: Small and focused interfaces
interface Workable {
  work(): void;
}
interface Feedable {
  eat(): void;
}
```

- **Dependency Inversion (DIP)**: Depend on abstractions (interfaces), not concrete implementations. High-level modules must not depend directly on low-level modules.

```typescript
// ❌ Wrong: Direct dependency on concrete implementation
class OrderService {
  private mysqlDb = new MySQLDatabase();
}

// ✅ Correct: Depends on an abstraction
class OrderService {
  constructor(private db: DatabaseInterface) {}
}
```

### Clean Code & Design Principles

- **DRY (Don't Repeat Yourself)**: Avoid duplication of logic. If the same code exists in two or more places, extract it into a reusable function or module.

- **KISS (Keep It Simple, Stupid)**: Avoid unnecessary complexity. Prioritize readability and simplicity. A simple working solution beats a complex "elegant" one.

- **YAGNI (You Ain't Gonna Need It)**: Do not add functionality until it is actually needed. Do not write code for hypothetical future requirements.

- **Clean Code / Readability**: Use meaningful variable and function names, consistent formatting, and simple logic. Code should be self-documenting.

- **Abstraction**: Hide internal complexity and only expose necessary interfaces. Abstraction layers help isolate changes and reduce coupling.

### Best Practices for Implementation

- **Test-Driven Development (TDD)**: Write tests before implementing code. Follow RED-GREEN-REFACTOR strictly.

- **Continuous Refactoring**: Regularly improve code structure without changing behavior. Boy Scout Rule: leave code better than you found it.

- **Code Reviews**: Utilize peer reviews to maintain code quality, standards, and consistency.

- **Security by Design**: Validate all input, sanitize data, and manage dependencies regularly. Security is a foundation, not an afterthought.

```
Basic security checklist:
- Validate and sanitize all user input
- Use parameterized queries (prevent SQL injection)
- Never hardcode secrets or credentials in source code
- Update dependencies regularly for security patches
- Apply the principle of least privilege
```

### Agent Enforcement Table

| Principle | How the Agent Applies It |
|---|---|
| SRP | Every function/class/module handles only one responsibility |
| OCP | Use patterns that allow extension without modification |
| LSP | Ensure inheritance and polymorphism are correctly applied |
| ISP | Create small, focused interfaces |
| DIP | Inject dependencies through constructors or parameters |
| DRY | Identify and eliminate code duplication |
| KISS | Choose the simplest solution that fulfills the requirement |
| YAGNI | Only implement what is requested — nothing more |
| TDD | Write tests before implementation — always |
| Security | Always validate input and follow security best practices |

---

## 5. Additional Rules

### Code Quality

- Always follow best practices from official documentation (fetched via Context7).
- Use TypeScript if the project already uses TypeScript.
- Add adequate error handling in all code paths.
- Write clean, readable, and well-documented code.

### Error Handling

- Error messages shown to users should be descriptive and in Bahasa Indonesia.
- Internal technical logs may use English conventions.
- When debugging, explain the process and findings in Bahasa Indonesia.

---

## Quick Reference

| Rule | Application |
|---|---|
| Response language | **Always Bahasa Indonesia** |
| Code language | English (variables, functions, syntax) |
| Code comments | Bahasa Indonesia |
| Context7 | Mandatory for every tech stack reference |
| Documentation priority | Context7 > internal/training knowledge |
| Coding principles | SOLID, DRY, KISS, YAGNI — always enforced |
| Testing | TDD: RED-GREEN-REFACTOR — no code before tests |
| Debugging | systematic-debugging + verification-before-completion |
| Security | Security by design — validate input, sanitize data |
| Refactoring | Continuous improvement — Boy Scout Rule |
| Communication with user | **Always in Bahasa Indonesia** |
