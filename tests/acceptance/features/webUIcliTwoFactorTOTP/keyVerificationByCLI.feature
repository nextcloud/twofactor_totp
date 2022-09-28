@webUI
Feature: Use the CLI to verify OTP keys for users
  As an admin
  I want to be able to use the CLI to verify OTP keys for users
  So that I can manage OTP for users

  Note: this feature is testing the CLI, but needs webUI steps for scenario setup in "given" steps

  Background:
    Given these users have been created with default attributes and large skeleton files:
      | username |
      | Alice    |
      | new-user |
    And using OCS API version "2"

  Scenario: Verifying secret for the user having no secret should fail
    When the administrator invokes occ command "twofactor_totp:set-secret-verification-status -u new-user true"
    Then the command should have failed with exit code 1
    And the command output should contain the text "User has no secret: new-user"
    And user "new-user" should be able to access a skeleton file

  Scenario: Unverifying secret for the user having no secret should fail
    When the administrator invokes occ command "twofactor_totp:set-secret-verification-status -u new-user false"
    Then the command should have failed with exit code 1
    And the command output should contain the text "User has no secret: new-user"
    And user "new-user" should be able to access a skeleton file

  @issue-91
  Scenario: Verifying secret to not existing user should fail
    When the administrator invokes occ command "twofactor_totp:set-secret-verification-status -u NEUser true"
    Then the command should have been successful
    # Then the command should have failed with exit code 1
    And the command output should contain the text "User NEUser does not exist"

  Scenario: Verifying secret from occ command should work
    Given user "Alice" has logged in using the webUI
    And the user has browsed to the personal security settings page
    And the user has activated TOTP Second-factor auth but not verified
    When the administrator invokes occ command "twofactor_totp:set-secret-verification-status -u Alice true"
    Then the command should have been successful
    And the command output should contain the text "The secret of Alice is verified"
    And user "Alice" using password "%regularuser%" should not be able to download file "textfile0.txt"

  Scenario: Unverifying secret from occ command should work
    Given user "Alice" has logged in using the webUI
    And the user has browsed to the personal security settings page
    And the user has activated TOTP Second-factor auth but not verified
    And the administrator has invoked occ command "twofactor_totp:set-secret-verification-status -u Alice true"
    When the administrator invokes occ command "twofactor_totp:set-secret-verification-status -u Alice false"
    Then the command should have been successful
    And the command output should contain the text "The secret of Alice is unverified"
    And user "Alice" should be able to access a skeleton file

  @issue-91
  Scenario: Verifying multiple users containing one not existing user should work for other users, but fail
    Given user "Alice" has logged in using the webUI
    And the user has browsed to the personal security settings page
    And the user has activated TOTP Second-factor auth but not verified
    When the administrator invokes occ command "twofactor_totp:set-secret-verification-status -u Alice -u NEUser true"
    Then the command should have been successful
    # Then the command should have failed with exit code 1
    And the command output should contain the text "The secret of Alice is verified"
    And the command output should contain the text "User NEUser does not exist"
    And user "Alice" using password "%regularuser%" should not be able to download file "textfile0.txt"

  Scenario: Verifying multiple users containing one having no secret set should work for other users, but fail
    Given user "Alice" has logged in using the webUI
    And the user has browsed to the personal security settings page
    And the user has activated TOTP Second-factor auth but not verified
    When the administrator invokes occ command "twofactor_totp:set-secret-verification-status -u Alice -u new-user true"
    Then the command should have failed with exit code 1
    And the command output should contain the text "The secret of Alice is verified"
    And the command output should contain the text "User has no secret: new-user"
    And user "Alice" using password "%regularuser%" should not be able to download file "textfile0.txt"
    And user "new-user" should be able to access a skeleton file

  @issue-91
  Scenario: Unverifying multiple users containing one not existing user should work for other users, but fail
    Given user "Alice" has logged in using the webUI
    And the user has browsed to the personal security settings page
    And the user has activated TOTP Second-factor auth but not verified
    And the administrator has invoked occ command "twofactor_totp:set-secret-verification-status -u Alice true"
    When the administrator invokes occ command "twofactor_totp:set-secret-verification-status -u Alice -u NEUser false"
    Then the command should have been successful
    # Then the command should have failed with exit code 1
    And the command output should contain the text "The secret of Alice is unverified"
    And the command output should contain the text "User NEUser does not exist"
    And user "Alice" should be able to access a skeleton file

  Scenario: Unverifying multiple users containing one having no secret set should work for other users, but fail
    Given user "Alice" has logged in using the webUI
    And the user has browsed to the personal security settings page
    And the user has activated TOTP Second-factor auth but not verified
    And the administrator has invoked occ command "twofactor_totp:set-secret-verification-status -u Alice true"
    When the administrator invokes occ command "twofactor_totp:set-secret-verification-status -u Alice -u new-user false"
    Then the command should have failed with exit code 1
    And the command output should contain the text "The secret of Alice is unverified"
    And the command output should contain the text "User has no secret: new-user"
    And user "Alice" should be able to access a skeleton file
    And user "new-user" should be able to access a skeleton file

  Scenario: Verifying all users that use TOTP should work
    Given user "Alice" has logged in using the webUI
    And the user has browsed to the personal security settings page
    And the user has activated TOTP Second-factor auth but not verified
    When the administrator invokes occ command "twofactor_totp:set-secret-verification-status --all true"
    Then the command should have been successful
    And the command output should contain the text "The status of all TOTP secrets has been set to verified"
    And user "Alice" using password "%regularuser%" should not be able to download file "textfile0.txt"
    And user "new-user" should be able to access a skeleton file

  Scenario: Unverifying all users that use TOTP should work
    Given user "Alice" has logged in using the webUI
    And the user has browsed to the personal security settings page
    And the user has activated TOTP Second-factor auth but not verified
    And the administrator has invoked occ command "twofactor_totp:set-secret-verification-status --all true"
    When the administrator invokes occ command "twofactor_totp:set-secret-verification-status --all false"
    Then the command should have been successful
    And the command output should contain the text "The status of all TOTP secrets has been set to unverified"
    And user "Alice" should be able to access a skeleton file
    And user "new-user" should be able to access a skeleton file

  Scenario: Deleted user having TOTP enabled recreated should not ask for password
    Given user "Alice" has logged in using the webUI
    And the user has browsed to the personal security settings page
    And the user has activated TOTP Second-factor auth but not verified
    And the administrator has invoked occ command "twofactor_totp:set-secret-verification-status --all true"
    When the administrator deletes user "Alice" using the provisioning API
    And the administrator creates user "Alice" using the provisioning API
    Then user "Alice" should be able to access a skeleton file
