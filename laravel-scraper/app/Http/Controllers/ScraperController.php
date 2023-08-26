<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Goutte\Client;

class ScraperController extends Controller
{
    private $results = array();
    
    // connects to db and inserts collected data 
    public function write_row($title, $author, $pages_count, $language, $size, $pdf) {
        $servername = "localhost:3308";
        $username = "root";
        $password = "root";
        $dbname = "mindluster";

        $conn = mysqli_connect($servername, $username, $password, $dbname);

        $query = "INSERT INTO scraping VALUES (NULL,'$title','$author', '$language', '$size', '$pages_count', '$pdf')";
        // echo($query);
        mysqli_query($conn, $query);
    }
    public function scraper() {
        // setting up scraper
        $client = new Client();
        $url = 'https://www.kotobati.com/section/%D8%B1%D9%88%D8%A7%D9%8A%D8%A7%D8%AA';
        $page = $client->request('GET', $url);
        
        // due to there being 400+ pages, the while loop collects data from only 4 pages for testing purposes
        // however if the condition is changed so that the loop runs infinitely, it will collect data from all pages of the website
        // and break when there are more no pages to load
        $i = 1;
        $page_limit = 5;

        while ($i < $page_limit){
            // overriding the 120 seconds time limit for php apps so that the application can run until termination
            set_time_limit(0);

            //gathering every book displayed on the page and writing their links to "results" array
            $page->filter('.book-teaser > h3 > a')->each(function ($node){
                $href = $node->extract(array('href'));
                $this->results[$node->text()] = $href[0];
            });

            // loop to click on every book displayed on the page and gathering the required data from it
            foreach($this->results as $x => $val) {
                $link = $page->selectLink($x)->link();
                $subpage = $client->click($link);

                $title = $subpage->filter("#block-ktobati-content > article > div.article-body > div > div.row.justify-content-center > div > div > div.media.row > div.media-body.col-md-10 > h2")->text();
                $author = $subpage->filter("#block-ktobati-content > article > div.article-body > div > div.row.justify-content-center > div > div > div.media.row > div.media-body.col-md-10 > p:nth-child(2) > a")->text();
                try {
                    $pages_count = $subpage->filter("#block-ktobati-content > article > div.article-body > div > div.row.justify-content-center > div > div > div.media.row > div.media-body.col-md-10 > ul:nth-child(4) > li:nth-child(1) > p:nth-child(2) > span")->text();
                } catch (\Throwable $th) {
                    $pages_count = "";
                }
                try {
                    $size = $subpage->filter("#block-ktobati-content > article > div.article-body > div > div.row.justify-content-center > div > div > div.media.row > div.media-body.col-md-10 > ul:nth-child(4) > li:nth-child(3) > p:nth-child(2)")->text();
                } catch (\Throwable $th) {
                    $size = "";
                }
                try {
                    $language = $subpage->filter("#block-ktobati-content > article > div.article-body > div > div.row.justify-content-center > div > div > div.media.row > div.media-body.col-md-10 > ul:nth-child(4) > li:nth-child(2) > p:nth-child(2)")->text();
                } catch (\Throwable $th) {
                    $language = "";
                }
                $pdf = "pdf";

                // writing the gathered data to database
                $this->write_row($title, $author, $pages_count, $size, $language, $pdf);
            }

            // emptying results array for next page's books and incrementing i
            $this->results = array_diff($this->results, $this->results);
            $i++;

            // in case of changing the condition of the while loop so it runs infinitely, this try/catch block ensure that the loop eventually terminates
            // it does so by checking for the existence of the "المزيد" button, and when it's not found on the current page, the loop breaks
            try {
                $next = $page->filter('.pager__item > .button')->text();
                $load = $page->selectLink($next)->link();
                $page = $client->click($load);
            } catch (\Throwable $th) {
                echo("in catch");
                break;
            }
        }
    }
}
