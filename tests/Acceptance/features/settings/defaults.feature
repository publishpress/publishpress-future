Feature: Settings - Tab Defaults
  In order to configure the defaults options
  As an admin
  I want to use the Defaults tab on the settings

  Background:
    Given the user "teddy" exists with role "administrator"
    And I am logged in as "teddy"
    And only the plugins "post-expirator, pre-tests" are active
    And I have option "permalink_structure" as "/%postname%/"
    And I am on the settings page in the Defaults tab

  Scenario Outline: Custom Date format
    Given post "apost" exists
    And post "apost" is set to expire in seven days at noon as Draft
    And settings is set to show in the post footer
    When I fill Date Format with "<date_format>"
    And I save the changes
    And I view the post "apost"
    Then I see the expiration date in the post footer with format "<date_format>"

  Examples:
  | date_format |
  | l F jS, Y   |
  | Y-m-d       |
  | l \d. j F Y |

  Scenario Outline: Custom Time format
    Given post "apost" exists
    And post "apost" is set to expire in seven days at noon as Draft
    And settings is set to show in the post footer
    When I fill Time Format with "<time_format>"
    And I save the changes
    And I view the post "apost"
    Then I see the expiration time in the post footer with format "<time_format>"

  Examples:
  | time_format |
  | g:ia   |
  | g:i a  |
  | H:i    |
  | H:i:s  |
