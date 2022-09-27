@webUI @insulated
Feature: TOTP with guest users
  As a guest user
  I want to be able to use TOTP
  So that I can use 2FA

  Scenario: Guest user can use 2FA and login with TOTP
    Given the administrator has created guest user "guest" with email "guest@example.com"
    And user "guest" has logged in using the webUI
    And the user has browsed to the personal security settings page
    And the user has activated TOTP Second-factor auth but not verified
    When the user adds one-time key generated from the secret key using the webUI
    Then the TOTP secret key should be verified on the webUI
    When the user re-logs in as "guest" to the two-factor authentication verification page
    And the user adds one-time key generated from the secret key on the verification page on the webUI
    Then the user should be redirected to a webUI page with the title "Files - %productname%"
