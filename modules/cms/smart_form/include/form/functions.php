<?
// 5.2.2024 @mm + chatgpt
//this function make confirmfild for formulars
// Beispielwerte für die Funktion
// $form_id = 'meinFormular';
// $arr = array(
//     'ajax' => array(
//         'confirmation' => true, // Oder spezifische Einstellungen als Array
//         'text' => array(
//             'positive' => 'Send',
//             'negative' => 'Cancel',
//             'header' => 'Confirm Submission',
//             'content' => 'Do you really want to submit the form?'
//         ),
//         'colors' => array(
//             'positive' => 'blue',
//             'negative' => 'orange'
//         )
//     )
// );

// // Funktionsaufruf
// echo generateConfirmationModal($form_id, $arr);

function generateConfirmationModal($form_id, $arr)
{
    if (isset($arr['ajax']['confirmation'])) {
        // Default values
        $defaultSettings = array(
            'text' => array(
                'positive' => 'Yes',
                'negative' => 'No',
                'header' => 'Submit Form?',
                'content' => 'Are you sure you want to submit this form?'
            ),
            'colors' => array(
                'positive' => 'green',
                'negative' => 'red'
            )
        );

        // Check if confirmation is simply enabled with true or has specific settings
        $confirmationSettings = $arr['ajax']['confirmation'] === true ? array() : (is_array($arr['ajax']['confirmation']) ? $arr['ajax']['confirmation'] : array());

        // Merge defaults with provided settings, handling the case where confirmation is true
        $settings = array_replace_recursive($defaultSettings, $confirmationSettings);

        return '
        <div class="ui mini modal" id="confirmationModal_' . $form_id . '">
          <div class="header">' . htmlspecialchars($settings['text']['header']) . '</div>
          <div class="content">
            <p>' . htmlspecialchars($settings['text']['content']) . '</p>
          </div>
          <div class="actions">
            <div class="ui ' . htmlspecialchars($settings['colors']['negative']) . ' negative button">' . htmlspecialchars($settings['text']['negative']) . '</div>
            <div class="ui ' . htmlspecialchars($settings['colors']['positive']) . ' positive button">' . htmlspecialchars($settings['text']['positive']) . '</div>
          </div>
        </div>
        ';
    }
}
