@webUI @insulated @mailhog
Feature: TOTP with guest users
  As a guest user
  I want to be able to use TOTP
  So that I can use 2FA

  Background:
    Given user "Alice" has been created with default attributes and without skeleton files
    And the administrator has created guest user "guest" with email "guest@example.com"
    And user "Alice" has created folder "/shared"

  Scenario: Guest user can use 2FA and login with TOTP
    Given user "Alice" has shared folder "/shared" with user "guest@example.com"
    And guest user "guest" has registered and set password to "simplepass"
    And the administrator has invoked occ command "twofactor_totp:set-secret-verification-status -u guest true"
    And user "guest@example.com" logs in using the webUI
    And the user has browsed to the personal security settings page
    And the user has activated TOTP Second-factor auth but not verified
    And the user has added one-time key generated from the secret key
    When the user re-logs in as "guest@example.com" to the two-factor authentication verification page
    And the user adds one-time key generated from the secret key on the verification page on the webUI
    Then the user should be redirected to a webUI page with the title "Files - %productname%"
    And folder "shared" should be listed on the webUI

  Scenario: Guest user can use 2FA, login with TOTP and upload on a shared resource
    Given user "Alice" has shared folder "/shared" with user "guest@example.com" with permissions "all"
    And guest user "guest" has registered and set password to "simplepass"
    And the administrator has invoked occ command "twofactor_totp:set-secret-verification-status -u guest true"
    And user "guest@example.com" logs in using the webUI
    And the user has browsed to the personal security settings page
    And the user has activated TOTP Second-factor auth but not verified
    And the user has added one-time key generated from the secret key
    When the user re-logs in as "guest@example.com" to the two-factor authentication verification page
    And the user adds one-time key generated from the secret key on the verification page on the webUI
    And the user opens folder "shared" using the webUI
    And the user uploads file "data.zip" using the webUI
    Then file "data.zip" should be listed on the webUI
    And user "guest1" using password "simplepass" should not be able to download file "shared/data.zip"
