@webUI @insulated
Feature: Use the webUI to verify OTP keys
  As a user
  I want to be able to use the webUI to activate and verify my OTP key
  So that I can use two factor authentication

  Background:
    Given user "newly-created-user" has been created with default attributes and large skeleton files
    And using OCS API version "2"


  Scenario: Verify using the key generated from the secret
    Given user "newly-created-user" has logged in using the webUI
    And the user has browsed to the personal security settings page
    And the user has activated TOTP Second-factor auth but not verified
    When the user adds one-time key generated from the secret key using the webUI
    Then the TOTP secret key should be verified on the webUI
    And user "newly-created-user" using password "%regularuser%" should not be able to download file "textfile0.txt"


  Scenario: The secret code from qr code should match with the one displayed in the page
    Given user "newly-created-user" has logged in using the webUI
    And the user has browsed to the personal security settings page
    When the user activates TOTP Second-factor auth but does not verify
    Then the secret code from QR code should match with the one displayed on the webUI


  Scenario: Totp workflow
    Given user "newly-created-user" has logged in using the webUI
    And the user has browsed to the personal security settings page
    And the user has activated TOTP Second-factor auth but not verified
    When the user adds one-time key generated from the secret key using the webUI
    Then the TOTP secret key should be verified on the webUI
    When the user re-logs in as "newly-created-user" to the two-factor authentication verification page
    And the user adds one-time key generated from the secret key on the verification page on the webUI
    Then the user should be redirected to a webUI page with the title "Files - %productname%"


  Scenario Outline: User provides wrong key on the verification page
    Given user "newly-created-user" has logged in using the webUI
    And the user has browsed to the personal security settings page
    And the user has activated TOTP Second-factor auth but not verified
    When the user adds one-time key generated from the secret key using the webUI
    Then the TOTP secret key should be verified on the webUI
    When the user re-logs in as "newly-created-user" to the two-factor authentication verification page
    And the user adds one-time key "<key>" on the verification page on the webUI
    Then the user should see an error message on the verification page saying "An error occurred while verifying the token"
    Examples:
      | key   |
      | abcde |
      | 11111 |
      | ab2x6 |
      | 1     |


  Scenario: User cancels the the key verification on the verification page
    Given user "newly-created-user" has logged in using the webUI
    And the user has browsed to the personal security settings page
    And the user has activated TOTP Second-factor auth but not verified
    When the user adds one-time key generated from the secret key using the webUI
    Then the TOTP secret key should be verified on the webUI
    When the user re-logs in as "newly-created-user" to the two-factor authentication verification page
    And the user cancels the verification using the webUI
    Then the user should be redirected to the login page
