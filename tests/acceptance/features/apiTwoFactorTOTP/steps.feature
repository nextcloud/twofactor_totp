@webUI
Feature: Testing Two factor TOTP
  As a admin
  I want to send otp
  So that I can check the result of last verification request

  Background:
    Given these users have been created with default attributes and skeleton files:
      | username |
      | user0    |
    And using OCS API version "1"

  Scenario: Administrator tries to verify OTP key for user using correct key
    Given user "user0" has logged in using the webUI
    And the user has browsed to the personal security settings page
    And the user has activated TOTP Second-factor auth but not verified
    When the administrator tries to verify with the one-time key generated from the secret key for user "user0"
    Then the OCS status code should be "100"
    And the HTTP status code should be "200"
    And the result of the last verification request should be true

  Scenario: Administrator tries to verify OTP key for user using wrong key
    Given user "user0" has logged in using the webUI
    And the user has browsed to the personal security settings page
    And the user has activated TOTP Second-factor auth but not verified
    When the administrator tries to verify with an invalid key "random" for user "user0"
    Then the OCS status code should be "100"
    And the HTTP status code should be "200"
    And the result of the last verification request should be false

  Scenario: Administrator tries to verify OTP key for a user that does not exist
    When the administrator tries to verify with the one-time key generated from the secret key for user "undefined"
    Then the OCS status code should be "404"
    And the HTTP status code should be "200"
    And the result of the last verification request should be false