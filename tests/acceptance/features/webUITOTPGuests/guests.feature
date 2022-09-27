@webUI @insulated @mailhog
Feature: TOTP with guest users
  As a guest user
  I want to be able to use TOTP
  So that I can use 2FA

  Scenario: Guest user can use 2FA and login with TOTP
    Given user "Alice" has been created with default attributes and without skeleton files
    Given the administrator has created guest user "guest" with email "guest@example.com"
    And user "Alice" has created folder "/tmp"
    And user "Alice" has shared folder "/tmp" with user "guest@example.com"
    When guest user "guest" registers and sets password to "password"
    And user "guest@example.com" logs in using the webUI
    Then the user should be redirected to a webUI page with the title "Files - %productname%"
    And folder "tmp" should be listed on the webUI
    Given the user has browsed to the personal security settings page
    And the user has activated TOTP Second-factor auth but not verified
    When the user adds one-time key generated from the secret key using the webUI
    Then the TOTP secret key should be verified on the webUI
    When the user re-logs in as "guest@example.com" to the two-factor authentication verification page
    And the user adds one-time key generated from the secret key on the verification page on the webUI
    Then the user should be redirected to a webUI page with the title "Files - %productname%"
