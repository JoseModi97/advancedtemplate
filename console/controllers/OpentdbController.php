<?php
namespace console\controllers;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use yii\console\Controller;
use yii\helpers\Console;
use frontend\models\Category;
use frontend\models\Question;
use frontend\models\Answer;

class OpentdbController extends Controller
{
    public function actionFetch($amount = 10)
    {
        $client = new Client();
        $maxBatch = 10;
        $inserted = 0;

        while ($amount > 0) {
            $batchSize = min($maxBatch, $amount);
            $url = "https://opentdb.com/api.php?amount={$batchSize}";
            $this->stdout("Fetching $batchSize questions...\n", Console::FG_CYAN);

            try {
                $response = $client->get($url, [
                    'http_errors' => false,
                    'timeout' => 10,
                ]);

                $status = $response->getStatusCode();

                if ($status == 429) {
                    $this->stderr("Rate limited (429). Waiting 15 seconds...\n", Console::FG_YELLOW);
                    sleep(15);
                    continue;
                }

                if ($status !== 200) {
                    $this->stderr("Error: HTTP $status returned from OpenTDB\n", Console::FG_RED);
                    break;
                }

                $data = json_decode($response->getBody(), true);
                if ($data['response_code'] !== 0) {
                    $this->stderr("API logic error (code {$data['response_code']})\n", Console::FG_RED);
                    break;
                }

                foreach ($data['results'] as $entry) {
                    $questionText = html_entity_decode($entry['question']);
                    $hash = sha1($questionText);

                    if (Question::find()->where(['question_hash' => $hash])->exists()) {
                        $this->stdout("Skipped duplicate.\n", Console::FG_BLUE);
                        continue;
                    }

                    $category = Category::findOne(['name' => $entry['category']]);
                    if (!$category) {
                        $category = new Category([
                            'name' => $entry['category'],
                            'created_at' => time(),
                            'updated_at' => time(),
                        ]);
                        $category->save();
                    }

                    $question = new Question([
                        'category_id' => $category->id,
                        'type' => $entry['type'],
                        'difficulty' => $entry['difficulty'],
                        'question' => $questionText,
                        'question_hash' => $hash,
                        'created_at' => time(),
                        'updated_at' => time(),
                    ]);

                    if (!$question->save()) {
                        $this->stderr("Failed to save question.\n", Console::FG_RED);
                        continue;
                    }

                    $correct = html_entity_decode($entry['correct_answer']);
                    $answers = array_map('html_entity_decode', $entry['incorrect_answers']);
                    $answers[] = $correct;
                    shuffle($answers);

                    foreach ($answers as $a) {
                        $answer = new Answer([
                            'question_id' => $question->id,
                            'answer' => $a,
                            'is_correct' => ($a === $correct),
                            'created_at' => time(),
                            'updated_at' => time(),
                        ]);
                        $answer->save();
                    }

                    $inserted++;
                    $this->stdout("Inserted: $questionText\n", Console::FG_GREEN);
                }

                $amount -= $batchSize;
                sleep(1); // Be polite to the API

            } catch (RequestException $e) {
                $this->stderr("HTTP request failed: " . $e->getMessage() . "\n", Console::FG_RED);
                $this->stdout("Retrying in 10 seconds...\n", Console::FG_YELLOW);
                sleep(10);
            }
        }

        $this->stdout("Finished. Inserted $inserted new questions.\n", Console::FG_YELLOW);
    }
}
