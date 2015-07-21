<?php
/**
 * HttpPostAfterSurveyComplete Plugin for LimeSurvey
 * Exports each survey response by POST request to a HTTP of your choice
 *
 * @author Christoph Peschel <hi@chrp.es>
 * @copyright 2015 Christoph Peschel <hi@chrp.es>
 * @license AGPL v3
 * @version 0.1
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Affero Public License for more details.
 *
 * See https://www.gnu.org/licenses/agpl-3.0.de.html
 *
 */
class HttpPostAfterSurveyComplete extends PluginBase {
  protected $storage = 'DbStorage';
  static protected $name = 'Post after survey is complete';
  static protected $description = 'Sends a each survey result as JSON POST request to a HTTP server.';

  public function __construct(PluginManager $manager, $id) {
    parent::__construct($manager, $id);
    $this->subscribe('afterSurveyComplete');
  }

  /**
   * Plugin settings
   */
  protected $settings = array(
    'url' => array(
      'type'=>'string',
      'label'=>'URL which should receive completed survey results',
      'default'=>'http://posttestserver.com/post.php?dir=chrp'
    ),
    'showServerResponse' => array(
      'type'=>'boolean',
      'label'=>'Show server response at the end of the survey',
      'default'=>'1'
    )
  );

  /**
   * This event is fired after the survey has been completed.
   * @param PluginEvent $event
   */
  public function afterSurveyComplete() {
    $event = $this->getEvent();

    $data = $this->api->getResponse($event->get('surveyId'), $event->get('responseId'));
    $url = $this->get('url');

    $options = array(
      'http' => array(
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query($data)
      )
    );
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    if ($this->get('showServerResponse'))
      $event->getContent($this)->addContent('<p><pre>' . print_r($result, true) . '</pre></p><hr/>');
  }
}
?>
