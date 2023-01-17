<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;

class SitemapController extends Controller
{

  /**
   * Index
   * @return Response
   */
  public function index(): Response
  {
    $sitemapConfig = config('sitemap');
    $path = $sitemapConfig['path'] . '/' . $sitemapConfig['index_name'] . '.xml';
    return $this->renderXmlFile($path);
  }

  /**
   * Path
   * @param string $path
   * @return Response
   */
  public function path($path): Response
  {
    $sitemapConfig = config('sitemap');
    $path = $sitemapConfig['path'] . '/sitemap.' . $path . '.xml';
    return $this->renderXmlFile($path);
  }

  /**
   * Render xml file
   * @param string $path
   * @return xml file
   */
  private function renderXmlFile(string $path): Response
  {
    if (File::exists($path)) {
      return response(File::get($path), 200, [
        'Content-Type' => 'application/xml'
      ]);
    } else {
      abort(404);
    }
  }
}
