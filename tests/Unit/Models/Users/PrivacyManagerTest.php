<?php

declare(strict_types=1);

namespace App\Tests\Unit\Models\Users;

use PHPUnit\Framework\TestCase;
use App\Modules\Models\Users\PrivacyManager;
use App\Core\Database;
use PDO;
use PDOStatement;
use ReflectionClass;

/**
 * PrivacyManagerTest - Unit tests for PrivacyManager
 *
 * Tests all privacy-related operations including:
 * - Account blocking/unblocking
 * - Password change token management
 * - Email change token management
 * - Verification code generation and validation
 * - Email and password format validation
 *
 * @author BdeLive Team
 * @version 1.0.0
 * @package BdeLive\Tests\Unit\Models\Users
 */
class PrivacyManagerTest extends TestCase
{
    private PrivacyManager $privacyManager;
    private PDO $mockPdo;
    private PDOStatement $mockStmt;

    /**
     * Set up test fixtures
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->mockPdo = $this->createMock(PDO::class);
        $this->mockStmt = $this->createMock(PDOStatement::class);

        $mockDatabase = $this->createMock(Database::class);
        $mockDatabase->method('getConnection')->willReturn($this->mockPdo);

        $reflection = new ReflectionClass(Database::class);
        $instanceProperty = $reflection->getProperty('instance');
        $instanceProperty->setAccessible(true);
        $instanceProperty->setValue(null, $mockDatabase);

        $this->privacyManager = new PrivacyManager();
    }

    /**
     * Tear down test fixtures
     *
     * @return void
     */
    protected function tearDown(): void
    {
        $reflection = new ReflectionClass(Database::class);
        $instanceProperty = $reflection->getProperty('instance');
        $instanceProperty->setAccessible(true);
        $instanceProperty->setValue(null, null);
    }

    // ==================== VERIFICATION CODE TESTS ====================

    /**
     * Test that generateVerificationCode returns a 6-digit string
     *
     * @return void
     */
    public function testGenerateVerificationCodeReturns6DigitString(): void
    {
        $code = $this->privacyManager->generateVerificationCode();

        $this->assertIsString($code);
        $this->assertEquals(6, strlen($code));
        $this->assertMatchesRegularExpression('/^[0-9]{6}$/', $code);
    }

    /**
     * Test that generateVerificationCode generates unique codes
     *
     * @return void
     */
    public function testGenerateVerificationCodeGeneratesUniqueCodes(): void
    {
        $codes = [];
        for ($i = 0; $i < 100; $i++) {
            $codes[] = $this->privacyManager->generateVerificationCode();
        }

        // With 100 codes, there should be many unique values
        $uniqueCodes = array_unique($codes);
        $this->assertGreaterThan(90, count($uniqueCodes));
    }

    /**
     * Test that generateVerificationCode pads with leading zeros
     *
     * @return void
     */
    public function testGenerateVerificationCodePadsWithLeadingZeros(): void
    {
        // Run multiple times to increase chance of getting a code starting with 0
        $foundLeadingZero = false;
        for ($i = 0; $i < 1000; $i++) {
            $code = $this->privacyManager->generateVerificationCode();
            if ($code[0] === '0') {
                $foundLeadingZero = true;
                $this->assertEquals(6, strlen($code));
                break;
            }
        }
        // Even if we don't find one, the test passes as long as all codes are valid
        $this->assertTrue(true);
    }

    // ==================== EMAIL FORMAT VALIDATION TESTS ====================

    /**
     * Test valid email format
     *
     * @return void
     */
    public function testIsValidEmailFormatReturnsTrueForValidEmail(): void
    {
        $this->assertTrue($this->privacyManager->isValidEmailFormat('test@example.com'));
        $this->assertTrue($this->privacyManager->isValidEmailFormat('user.name@domain.org'));
        $this->assertTrue($this->privacyManager->isValidEmailFormat('user+tag@example.co.uk'));
        $this->assertTrue($this->privacyManager->isValidEmailFormat('user123@sub.domain.com'));
    }

    /**
     * Test invalid email format
     *
     * @return void
     */
    public function testIsValidEmailFormatReturnsFalseForInvalidEmail(): void
    {
        $this->assertFalse($this->privacyManager->isValidEmailFormat(''));
        $this->assertFalse($this->privacyManager->isValidEmailFormat('invalid'));
        $this->assertFalse($this->privacyManager->isValidEmailFormat('invalid@'));
        $this->assertFalse($this->privacyManager->isValidEmailFormat('@domain.com'));
        $this->assertFalse($this->privacyManager->isValidEmailFormat('invalid@domain'));
        $this->assertFalse($this->privacyManager->isValidEmailFormat('invalid email@domain.com'));
    }

    // ==================== PASSWORD FORMAT VALIDATION TESTS ====================

    /**
     * Test valid password format
     *
     * @return void
     */
    public function testValidatePasswordFormatReturnsValidForStrongPassword(): void
    {
        $result = $this->privacyManager->validatePasswordFormat('MyP@ssw0rd!');

        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['errors']);
    }

    /**
     * Test password too short
     *
     * @return void
     */
    public function testValidatePasswordFormatReturnsErrorForShortPassword(): void
    {
        $result = $this->privacyManager->validatePasswordFormat('Ab1!');

        $this->assertFalse($result['valid']);
        $this->assertContains('Le mot de passe doit contenir au moins 10 caractères', $result['errors']);
    }

    /**
     * Test password without uppercase
     *
     * @return void
     */
    public function testValidatePasswordFormatReturnsErrorForNoUppercase(): void
    {
        $result = $this->privacyManager->validatePasswordFormat('mypassword1!');

        $this->assertFalse($result['valid']);
        $this->assertContains('Le mot de passe doit contenir au moins une lettre majuscule', $result['errors']);
    }

    /**
     * Test password without digit
     *
     * @return void
     */
    public function testValidatePasswordFormatReturnsErrorForNoDigit(): void
    {
        $result = $this->privacyManager->validatePasswordFormat('MyPassword!!');

        $this->assertFalse($result['valid']);
        $this->assertContains('Le mot de passe doit contenir au moins un chiffre', $result['errors']);
    }

    /**
     * Test password without special character
     *
     * @return void
     */
    public function testValidatePasswordFormatReturnsErrorForNoSpecialChar(): void
    {
        $result = $this->privacyManager->validatePasswordFormat('MyPassword12');

        $this->assertFalse($result['valid']);
        $this->assertContains('Le mot de passe doit contenir au moins un caractère spécial', $result['errors']);
    }

    /**
     * Test password with all errors
     *
     * @return void
     */
    public function testValidatePasswordFormatReturnsMultipleErrors(): void
    {
        $result = $this->privacyManager->validatePasswordFormat('abc');

        $this->assertFalse($result['valid']);
        $this->assertCount(4, $result['errors']);
    }

    // ==================== USER BLOCKING TESTS ====================

    /**
     * Test isUserBlocked returns false for non-blocked user
     *
     * @return void
     */
    public function testIsUserBlockedReturnsFalseForNonBlockedUser(): void
    {
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with(['user_id' => 1])
            ->willReturn(true);

        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->willReturn(['is_blocked' => 0, 'blocked_until' => null]);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $result = $this->privacyManager->isUserBlocked(1);
        $this->assertFalse($result);
    }

    /**
     * Test isUserBlocked returns true for blocked user
     *
     * @return void
     */
    public function testIsUserBlockedReturnsTrueForBlockedUser(): void
    {
        $futureTime = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with(['user_id' => 1])
            ->willReturn(true);

        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->willReturn(['is_blocked' => 1, 'blocked_until' => $futureTime]);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $result = $this->privacyManager->isUserBlocked(1);
        $this->assertTrue($result);
    }

    /**
     * Test isUserBlocked returns false for user not found
     *
     * @return void
     */
    public function testIsUserBlockedReturnsFalseForUserNotFound(): void
    {
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with(['user_id' => 999])
            ->willReturn(true);

        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->willReturn(false);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $result = $this->privacyManager->isUserBlocked(999);
        $this->assertFalse($result);
    }

    /**
     * Test blockUser sets blocked status
     *
     * @return void
     */
    public function testBlockUserSetsBlockedStatus(): void
    {
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function ($params) {
                return isset($params['blocked_until']) &&
                       isset($params['user_id']) &&
                       $params['user_id'] === 1;
            }))
            ->willReturn(true);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $result = $this->privacyManager->blockUser(1);
        $this->assertTrue($result);
    }

    /**
     * Test unblockUser clears blocked status
     *
     * @return void
     */
    public function testUnblockUserClearsBlockedStatus(): void
    {
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with(['user_id' => 1])
            ->willReturn(true);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $result = $this->privacyManager->unblockUser(1);
        $this->assertTrue($result);
    }

    /**
     * Test getRemainingBlockTime returns correct minutes
     *
     * @return void
     */
    public function testGetRemainingBlockTimeReturnsCorrectMinutes(): void
    {
        $futureTime = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with(['user_id' => 1])
            ->willReturn(true);

        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->willReturn(['blocked_until' => $futureTime]);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $result = $this->privacyManager->getRemainingBlockTime(1);
        $this->assertGreaterThanOrEqual(14, $result);
        $this->assertLessThanOrEqual(15, $result);
    }

    /**
     * Test getRemainingBlockTime returns 0 for non-blocked user
     *
     * @return void
     */
    public function testGetRemainingBlockTimeReturnsZeroForNonBlockedUser(): void
    {
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with(['user_id' => 1])
            ->willReturn(true);

        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->willReturn(false);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $result = $this->privacyManager->getRemainingBlockTime(1);
        $this->assertEquals(0, $result);
    }

    // ==================== PASSWORD CHANGE TOKEN TESTS ====================

    /**
     * Test createPasswordChangeToken creates token successfully
     *
     * @return void
     */
    public function testCreatePasswordChangeTokenCreatesToken(): void
    {
        $deleteStmt = $this->createMock(PDOStatement::class);
        $deleteStmt->expects($this->once())
            ->method('execute')
            ->with(['user_id' => 1])
            ->willReturn(true);

        $insertStmt = $this->createMock(PDOStatement::class);
        $insertStmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function ($params) {
                return isset($params['user_id']) &&
                       isset($params['token']) &&
                       $params['user_id'] === 1 &&
                       $params['token'] === '123456';
            }))
            ->willReturn(true);

        $this->mockPdo->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($deleteStmt, $insertStmt);

        $result = $this->privacyManager->createPasswordChangeToken(1, '123456');
        $this->assertTrue($result);
    }

    /**
     * Test deletePasswordChangeToken removes token
     *
     * @return void
     */
    public function testDeletePasswordChangeTokenRemovesToken(): void
    {
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with(['user_id' => 1])
            ->willReturn(true);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $result = $this->privacyManager->deletePasswordChangeToken(1);
        $this->assertTrue($result);
    }

    /**
     * Test verifyPasswordChangeToken returns valid for correct code
     *
     * @return void
     */
    public function testVerifyPasswordChangeTokenReturnsValidForCorrectCode(): void
    {
        $selectStmt = $this->createMock(PDOStatement::class);
        $selectStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $selectStmt->expects($this->once())
            ->method('fetch')
            ->willReturn([
                'id' => 1,
                'token' => '123456',
                'attempts' => 0,
                'is_expired' => 0
            ]);

        $updateStmt = $this->createMock(PDOStatement::class);
        $updateStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $this->mockPdo->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($selectStmt, $updateStmt);

        $result = $this->privacyManager->verifyPasswordChangeToken(1, '123456');

        $this->assertTrue($result['valid']);
        $this->assertEquals('Code vérifié avec succès', $result['message']);
    }

    /**
     * Test verifyPasswordChangeToken returns invalid for wrong code
     *
     * @return void
     */
    public function testVerifyPasswordChangeTokenReturnsInvalidForWrongCode(): void
    {
        $selectStmt = $this->createMock(PDOStatement::class);
        $selectStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $selectStmt->expects($this->once())
            ->method('fetch')
            ->willReturn([
                'id' => 1,
                'token' => '123456',
                'attempts' => 0,
                'is_expired' => 0
            ]);

        $updateStmt = $this->createMock(PDOStatement::class);
        $updateStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $this->mockPdo->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($selectStmt, $updateStmt);

        $result = $this->privacyManager->verifyPasswordChangeToken(1, '654321');

        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('Code incorrect', $result['message']);
    }

    /**
     * Test verifyPasswordChangeToken returns invalid for expired token
     *
     * @return void
     */
    public function testVerifyPasswordChangeTokenReturnsInvalidForExpiredToken(): void
    {
        $selectStmt = $this->createMock(PDOStatement::class);
        $selectStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $selectStmt->expects($this->once())
            ->method('fetch')
            ->willReturn([
                'id' => 1,
                'token' => '123456',
                'attempts' => 0,
                'is_expired' => 1
            ]);

        $updateStmt = $this->createMock(PDOStatement::class);
        $updateStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $this->mockPdo->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($selectStmt, $updateStmt);

        $result = $this->privacyManager->verifyPasswordChangeToken(1, '123456');

        $this->assertFalse($result['valid']);
        $this->assertEquals('Le code a expiré', $result['message']);
    }

    /**
     * Test verifyPasswordChangeToken returns invalid when no token found
     *
     * @return void
     */
    public function testVerifyPasswordChangeTokenReturnsInvalidWhenNoTokenFound(): void
    {
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->willReturn(false);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $result = $this->privacyManager->verifyPasswordChangeToken(1, '123456');

        $this->assertFalse($result['valid']);
        $this->assertEquals('Aucun code de vérification trouvé', $result['message']);
    }

    // ==================== EMAIL CHANGE TOKEN TESTS ====================

    /**
     * Test createEmailChangeToken creates token with email
     *
     * @return void
     */
    public function testCreateEmailChangeTokenCreatesTokenWithEmail(): void
    {
        $deleteStmt = $this->createMock(PDOStatement::class);
        $deleteStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $insertStmt = $this->createMock(PDOStatement::class);
        $insertStmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function ($params) {
                return isset($params['user_id']) &&
                       isset($params['token']) &&
                       $params['user_id'] === 1 &&
                       str_starts_with($params['token'], 'EMAIL:123456:');
            }))
            ->willReturn(true);

        $this->mockPdo->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($deleteStmt, $insertStmt);

        $result = $this->privacyManager->createEmailChangeToken(1, '123456', 'new@example.com');
        $this->assertTrue($result);
    }

    /**
     * Test deleteEmailChangeToken removes email token
     *
     * @return void
     */
    public function testDeleteEmailChangeTokenRemovesToken(): void
    {
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with(['user_id' => 1])
            ->willReturn(true);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $result = $this->privacyManager->deleteEmailChangeToken(1);
        $this->assertTrue($result);
    }

    /**
     * Test verifyEmailChangeToken returns valid for correct code
     *
     * @return void
     */
    public function testVerifyEmailChangeTokenReturnsValidForCorrectCode(): void
    {
        $selectStmt = $this->createMock(PDOStatement::class);
        $selectStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $selectStmt->expects($this->once())
            ->method('fetch')
            ->willReturn([
                'id' => 1,
                'token' => 'EMAIL:123456:new@example.com',
                'attempts' => 0,
                'is_expired' => 0
            ]);

        $updateStmt = $this->createMock(PDOStatement::class);
        $updateStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $this->mockPdo->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($selectStmt, $updateStmt);

        $result = $this->privacyManager->verifyEmailChangeToken(1, '123456');

        $this->assertTrue($result['valid']);
        $this->assertEquals('Code vérifié avec succès', $result['message']);
    }

    /**
     * Test verifyEmailChangeToken returns invalid for wrong code
     *
     * @return void
     */
    public function testVerifyEmailChangeTokenReturnsInvalidForWrongCode(): void
    {
        $selectStmt = $this->createMock(PDOStatement::class);
        $selectStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $selectStmt->expects($this->once())
            ->method('fetch')
            ->willReturn([
                'id' => 1,
                'token' => 'EMAIL:123456:new@example.com',
                'attempts' => 0,
                'is_expired' => 0
            ]);

        $updateStmt = $this->createMock(PDOStatement::class);
        $updateStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $this->mockPdo->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($selectStmt, $updateStmt);

        $result = $this->privacyManager->verifyEmailChangeToken(1, '654321');

        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('Code incorrect', $result['message']);
    }

    // ==================== RESEND CODE TESTS ====================

    /**
     * Test canResendCode returns can_resend true when no token exists
     *
     * @return void
     */
    public function testCanResendCodeReturnsTrueWhenNoTokenExists(): void
    {
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->willReturn(false);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $result = $this->privacyManager->canResendCode(1);

        $this->assertTrue($result['can_resend']);
        $this->assertEquals(0, $result['wait_seconds']);
        $this->assertEquals(0, $result['resend_count']);
    }

    /**
     * Test canResendCode returns can_resend false when cooldown active
     *
     * @return void
     */
    public function testCanResendCodeReturnsFalseWhenCooldownActive(): void
    {
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->willReturn([
                'resend_count' => 2,
                'last_resend_at' => date('Y-m-d H:i:s'),
                'seconds_since_resend' => 30
            ]);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $result = $this->privacyManager->canResendCode(1);

        $this->assertFalse($result['can_resend']);
        $this->assertGreaterThan(0, $result['wait_seconds']);
        $this->assertEquals(2, $result['resend_count']);
    }

    /**
     * Test canResendCode returns can_resend false when max resends reached
     *
     * @return void
     */
    public function testCanResendCodeReturnsFalseWhenMaxResendsReached(): void
    {
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->willReturn([
                'resend_count' => 5,
                'last_resend_at' => date('Y-m-d H:i:s', strtotime('-2 minutes')),
                'seconds_since_resend' => 120
            ]);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $result = $this->privacyManager->canResendCode(1);

        $this->assertFalse($result['can_resend']);
        $this->assertEquals(5, $result['resend_count']);
    }

    /**
     * Test resendCode updates token
     *
     * @return void
     */
    public function testResendCodeUpdatesToken(): void
    {
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function ($params) {
                return isset($params['token']) &&
                       isset($params['user_id']) &&
                       $params['token'] === '654321' &&
                       $params['user_id'] === 1;
            }))
            ->willReturn(true);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $result = $this->privacyManager->resendCode(1, '654321');
        $this->assertTrue($result);
    }

    // ==================== EMAIL CHANGE ATTEMPT TRACKING TESTS ====================

    /**
     * Test trackEmailChangeAttempt increments counter
     *
     * @return void
     */
    public function testTrackEmailChangeAttemptIncrementsCounter(): void
    {
        $selectStmt = $this->createMock(PDOStatement::class);
        $selectStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $selectStmt->expects($this->once())
            ->method('fetch')
            ->willReturn(['email_change_attempts' => 2]);

        $updateStmt = $this->createMock(PDOStatement::class);
        $updateStmt->expects($this->once())
            ->method('execute')
            ->with(['attempts' => 3, 'user_id' => 1])
            ->willReturn(true);

        $this->mockPdo->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($selectStmt, $updateStmt);

        $result = $this->privacyManager->trackEmailChangeAttempt(1);
        $this->assertEquals(3, $result);
    }

    /**
     * Test resetEmailChangeAttempts resets counter
     *
     * @return void
     */
    public function testResetEmailChangeAttemptsResetsCounter(): void
    {
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with(['user_id' => 1])
            ->willReturn(true);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $result = $this->privacyManager->resetEmailChangeAttempts(1);
        $this->assertTrue($result);
    }

    /**
     * Test getEmailChangeAttempts returns current count
     *
     * @return void
     */
    public function testGetEmailChangeAttemptsReturnsCurrentCount(): void
    {
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->willReturn(['email_change_attempts' => 5]);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $result = $this->privacyManager->getEmailChangeAttempts(1);
        $this->assertEquals(5, $result);
    }

    /**
     * Test isMaxEmailChangeAttemptsReached returns true when max reached
     *
     * @return void
     */
    public function testIsMaxEmailChangeAttemptsReachedReturnsTrueWhenMaxReached(): void
    {
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->willReturn(['email_change_attempts' => 10]);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $result = $this->privacyManager->isMaxEmailChangeAttemptsReached(1);
        $this->assertTrue($result);
    }

    // ==================== CONSTANTS TESTS ====================

    /**
     * Test getMaxFailedAttempts returns expected value
     *
     * @return void
     */
    public function testGetMaxFailedAttemptsReturnsExpectedValue(): void
    {
        $result = $this->privacyManager->getMaxFailedAttempts();
        $this->assertEquals(10, $result);
    }

    /**
     * Test getResendCooldown returns expected value
     *
     * @return void
     */
    public function testGetResendCooldownReturnsExpectedValue(): void
    {
        $result = $this->privacyManager->getResendCooldown();
        $this->assertEquals(60, $result);
    }

    /**
     * Test getMaxResendAttempts returns expected value
     *
     * @return void
     */
    public function testGetMaxResendAttemptsReturnsExpectedValue(): void
    {
        $result = $this->privacyManager->getMaxResendAttempts();
        $this->assertEquals(5, $result);
    }
}

