<?php

declare(strict_types=1);

namespace App\Core\Session;

/**
 * SessionManager - PHP Session Management Wrapper
 *
 * Encapsulates $_SESSION access to prevent direct manipulation
 * and facilitate unit testing with dependency injection.
 *
 * Features:
 * - Safe session start/stop
 * - Flash messages (one-time messages)
 * - Session regeneration for security
 * - Consistent API for session operations
 *
 * @package App\Core\Session
 * @version 1.0.0
 */
class SessionManager
{
    private bool $started = false;

    /**
     * Start the PHP session
     * 
     * Safe to call multiple times - checks if session is already active.
     * 
     * @return void
     */
    public function start(): void
    {
        if ($this->started || session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_start();
        $this->started = true;
    }

    /**
     * Check if the session is started
     * 
     * @return bool True if session is active
     */
    public function isStarted(): bool
    {
        return $this->started || session_status() === PHP_SESSION_ACTIVE;
    }

    /**
     * Get a session value
     * 
     * @param string $key Session key
     * @param mixed $default Default value if key doesn't exist
     * @return mixed The session value or default
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $this->ensureStarted();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Set a session value
     * 
     * @param string $key Session key
     * @param mixed $value Value to store
     * @return void
     */
    public function set(string $key, mixed $value): void
    {
        $this->ensureStarted();
        $_SESSION[$key] = $value;
    }

    /**
     * Check if a session key exists
     * 
     * @param string $key Session key to check
     * @return bool True if key exists
     */
    public function has(string $key): bool
    {
        $this->ensureStarted();
        return isset($_SESSION[$key]);
    }

    /**
     * Remove a session value
     * 
     * @param string $key Session key to remove
     * @return void
     */
    public function remove(string $key): void
    {
        $this->ensureStarted();
        unset($_SESSION[$key]);
    }

    /**
     * Clear all session data
     * 
     * Removes all session variables but keeps the session active.
     * 
     * @return void
     */
    public function clear(): void
    {
        $this->ensureStarted();
        $_SESSION = [];
    }

    /**
     * Destroy the session completely
     * 
     * Clears all data, deletes session cookie, and destroys the session.
     * Use this for logout operations.
     * 
     * @return void
     */
    public function destroy(): void
    {
        $this->ensureStarted();
        $this->clear();

        $sessionName = session_name();
        if ($sessionName !== false && isset($_COOKIE[$sessionName])) {
            setcookie($sessionName, '', time() - 3600, '/');
        }

        session_destroy();
        $this->started = false;
    }

    /**
     * Set a flash message (available only once)
     * 
     * Flash messages are automatically deleted after being read once.
     * Useful for success/error messages after redirects.
     * 
     * @param string $key Message type (success, error, warning, info)
     * @param mixed $value Message content
     * @return void
     */
    public function flash(string $key, mixed $value): void
    {
        $this->set('_flash_' . $key, $value);
    }

    /**
     * Get and consume a flash message
     * 
     * Retrieves the flash message and immediately deletes it.
     * Subsequent calls return null.
     * 
     * @param string $key Message type
     * @param mixed $default Default value if flash doesn't exist
     * @return mixed The flash message or default
     */
    public function getFlash(string $key, mixed $default = null): mixed
    {
        $flashKey = '_flash_' . $key;
        $value = $this->get($flashKey, $default);
        $this->remove($flashKey);
        return $value;
    }

    /**
     * Check if a flash message exists
     * 
     * @param string $key Message type to check
     * @return bool True if flash message exists
     */
    public function hasFlash(string $key): bool
    {
        return $this->has('_flash_' . $key);
    }

    /**
     * Regenerate the session ID (security measure)
     * 
     * Should be called after login to prevent session fixation attacks.
     * 
     * @param bool $deleteOldSession Whether to delete the old session file
     * @return void
     */
    public function regenerate(bool $deleteOldSession = true): void
    {
        $this->ensureStarted();
        session_regenerate_id($deleteOldSession);
    }

    /**
     * Get the current session ID
     * 
     * @return string The session identifier
     */
    public function getId(): string
    {
        $this->ensureStarted();
        $id = session_id();
        return $id !== false ? $id : '';
    }

    /**
     * Ensure the session is started
     * 
     * Internal helper that automatically starts the session if needed.
     * 
     * @return void
     */
    private function ensureStarted(): void
    {
        if (!$this->isStarted()) {
            $this->start();
        }
    }
}
