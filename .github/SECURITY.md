# Security Policy

## Supported Versions

We actively support the following versions of Diffyne with security updates:

| Version | Supported          |
| ------- | ------------------ |
| 1.x.x   | :white_check_mark: |
| < 1.0   | :x:                |

## Reporting a Vulnerability

We take security vulnerabilities seriously. If you discover a security vulnerability in Diffyne, please follow these steps:

1. **Do NOT** open a public GitHub issue for security vulnerabilities.

2. **Email** the security team directly at: **xentixar@gmail.com**

3. Include the following information in your report:
   - Description of the vulnerability
   - Steps to reproduce the issue
   - Potential impact
   - Suggested fix (if any)

4. You will receive a response within **48 hours** acknowledging receipt of your report.

5. We will work with you to understand and resolve the issue quickly.

6. Once the vulnerability is confirmed and fixed, we will:
   - Release a security patch
   - Credit you in the security advisory (if desired)
   - Update this document with the vulnerability details

## Security Best Practices

When using Diffyne, please follow these security best practices:

- **Always verify state signatures**: Enable `diffyne.security.verify_state` in production
- **Use HTTPS**: Always serve Diffyne components over HTTPS
- **Keep dependencies updated**: Regularly update your Composer dependencies
- **Validate user input**: Always validate and sanitize user input on the server side
- **Use `#[Invokable]` attribute**: Only mark methods as invokable that should be callable from the client
- **Lock sensitive properties**: Use `#[Locked]` attribute for properties that should never be updated from the client

## Security Features

Diffyne includes several built-in security features:

- **State Signing**: HMAC-based state signature verification to prevent tampering
- **Method Protection**: Only methods marked with `#[Invokable]` can be called from the client
- **Property Locking**: Properties can be locked to prevent client-side updates
- **CSRF Protection**: Integrates with Laravel's CSRF protection

## Disclosure Policy

- Security vulnerabilities will be disclosed after a fix is available
- We will credit security researchers who responsibly disclose vulnerabilities
- Critical vulnerabilities will be patched within 7 days of confirmation

Thank you for helping keep Diffyne and its users safe!

