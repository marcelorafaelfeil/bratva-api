<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::group(['prefix' => 'store'], function() {
	// Products
	Route::group(['prefix' => 'products', 'middleware' => 'jwt'], function() {
		Route::post('', 'Store\ProductsController@newProduct');
		Route::put('', 'Store\ProductsController@updateProduct');
		Route::delete('', 'Store\ProductsController@removeProducts');
	});
	Route::get('products', 'Store\ProductsController@listProducts');
	Route::get('product/{url}/view', 'Store\ProductsController@viewProduct');
	Route::get('product/{id}/viewById', 'Store\ProductsController@viewProductById');
	Route::put('product/featuredImage', 'Store\ProductsController@featuredImage');

	// Brands
	Route::post('brands', 'Store\BrandsController@newBrand');
	Route::put('brands', 'Store\BrandsController@updateBrand');
	Route::delete('brands', 'Store\BrandsController@removeBrands');
	Route::get('brands', 'Store\BrandsController@listBrands');
	Route::get('brands/view', 'Store\BrandsController@viewBrand');
	Route::get('brands/products', 'Store\BrandsController@listProductsOfBrand');

	// Categories
	Route::post('categories', 'Store\CategoriesController@newCategory');
	Route::put('categories', 'Store\CategoriesController@updateCategory');
	Route::delete('categories', 'Store\CategoriesController@removeCategories');
	Route::get('categories', 'Store\CategoriesController@listCategories');
	Route::get('categories/view', 'Store\CategoriesController@viewCategory');
	Route::get('categories/products', 'Store\CategoriesController@listProductsOfCategories');

	Route::get('currencies', 'Store\CurrenciesController@listCurrencies');

});

Route::group(['prefix' => 'bratva'], function() {
	// CheckCode
	Route::post('checkcode/import', 'Bratva\CheckCodeController@importCodes');
	Route::post('checkcode', 'Bratva\CheckCodeController@newCode');
	Route::delete('checkcode', 'Bratva\CheckCodeController@removeCodes');
});

Route::group(['prefix' => 'web'], function() {
	// Banners
	Route::group(['prefix' => 'banners'], function() {
		// Types
		Route::post('types', 'Website\BannersTypesController@newType');
		Route::put('types', 'Website\BannersTypesController@updateType');
		Route::delete('types', 'Website\BannersTypesController@removeTypes');
		Route::get('types', 'Website\BannersTypesController@listTypes');
		Route::get('types/banners', 'Website\BannersTypesController@listBannersByTypes');
		Route::get('types/view', 'Website\BannersTypesController@viewBannersTypesById');

		// Banners
		Route::post('', 'Website\BannersController@newBanner');
		Route::put('', 'Website\BannersController@updateBanner');
		Route::delete('', 'Website\BannersController@removeBanners');
		Route::get('', 'Website\BannersController@listBanners');
	});

	// Pages
	Route::post('pages', 'Website\PagesController@newPage');
	Route::put('pages', 'Website\PagesController@updatePage');
	Route::delete('pages', 'Website\PagesController@removePages');
	Route::get('pages', 'Website\PagesController@listPages');
	Route::get('pages/{code}/viewbycode', 'Website\PagesController@viewPageById');
	Route::get('pages/{url}/view', 'Website\PagesController@viewPageByUrl');
	Route::get('pages/{code}/viewById', 'Website\PagesController@viewPageById');
	Route::put('pages/featuredImage', 'Website\PagesController@featuredImage');

	// Menus
	Route::post('menu', 'Website\MenuController@newMenu');
	Route::put('menu', 'Website\MenuController@updateMenu');
	Route::delete('menu', 'Website\MenuController@removeMenus');
	Route::get('menu', 'Website\MenuController@listMenus');

	Route::get('checkcode', 'Bratva\CheckCodeController@checkCode');

	// E-mails
	Route::post('emails', 'Generic\EmailsController@newEmail');
	Route::put('emails', 'Generic\EmailsController@updateEmail');
	Route::delete('emails', 'Generic\EmailsController@removeEmails');
	Route::get('emails', 'Generic\EmailsController@listEmails');

	// Newsletters
	Route::post('newsletter', 'Website\NewsletterController@createNewsletter');
	/*Route::put('newsletter', 'Website\NewsletterController@updateNewsletter');*/
	Route::delete('newsletter', 'Website\NewsletterController@removeNewsletters');
	Route::get('newsletter', 'Website\NewsletterController@listNewsletters');
});

Route::group(['prefix' => 'auth'], function() {
	Route::post('', 'Auth\AuthController@Authentication');
	Route::post('users', 'Auth\RegisterController@add');
	Route::put('users', 'Auth\RegisterController@edit');
});

Route::group(['prefix' => 'upload'], function() {
	// Upload
	Route::post('images/{section}/{key}', 'Generic\ImagesControllers@uploadImages');
	Route::delete('images', 'Generic\ImagesControllers@removeImages');
});