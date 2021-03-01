@webUI
Feature: Use an API to verify OTP keys for users
  As an admin
  I want to be able to use an API to verify OTP keys for users
  So that I can remotely manage OTP for users

  Note: this feature is testing the API, but needs webUI steps for scenario setup in "given" steps

  Background:
    Given these users have been created with default attributes and large skeleton files:
      | username |
      | Alice    |

  Scenario Outline: Administrator tries to verify OTP key for user using correct key
    Given using OCS API version "<ocs_api_version>"
    And user "Alice" has logged in using the webUI
    And the user has browsed to the personal security settings page
    And the user has activated TOTP Second-factor auth but not verified
    When the administrator tries to verify with the one-time key generated from the secret key for user "Alice"
    Then the OCS status code should be "<ocs-code>"
    And the HTTP status code should be "<http-code>"
    And the result of the last verification request should be true
    Examples:
      | ocs_api_version | ocs-code | http-code |
      | 1               | 100      | 200       |
      | 2               | 100      | 200       |

  Scenario Outline: Administrator tries to verify OTP key for user using wrong key
    Given using OCS API version "<ocs_api_version>"
    And user "Alice" has logged in using the webUI
    And the user has browsed to the personal security settings page
    And the user has activated TOTP Second-factor auth but not verified
    When the administrator tries to verify with an invalid key "random" for user "Alice"
    Then the OCS status code should be "<ocs-code>"
    And the HTTP status code should be "<http-code>"
    And the result of the last verification request should be false
    Examples:
      | ocs_api_version | ocs-code | http-code |
      | 1               | 100      | 200       |
      | 2               | 100      | 200       |

  Scenario Outline: Administrator tries to verify OTP key for a user that does not exist
    Given using OCS API version "<ocs_api_version>"
    When the administrator tries to verify with the one-time key generated from the secret key for user "undefined"
    Then the OCS status code should be "<ocs-code>"
    And the HTTP status code should be "<http-code>"
    And the result of the last verification request should be false
    Examples:
      | ocs_api_version | ocs-code | http-code |
      | 1               | 404      | 200       |
      | 2               | 404      | 200       |
