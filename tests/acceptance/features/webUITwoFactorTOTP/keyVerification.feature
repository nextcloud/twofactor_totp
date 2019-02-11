@webUI @insulated
Feature: Testing Two factor TOTP
  As a admin
  I want to be able to verify secrets from webUI
  So that the users can use two factor auth from TOTP key

  Background:
    Given user "newly-created-user" has been created with default attributes
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