<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Shipping;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        if (User::count() == 0) {
            $user = new User();
            $user->name = 'Scott Miller';
            $user->email = 'scottmillar@gmail.com';
            $user->password = Hash::make('123456');
            $user->user_role = 'admin';

            $user->save();

            $user = new User();
            $user->name = 'Salman Abbas';
            $user->email = 'salmanabbas985@gmail.com';
            $user->password = Hash::make('123456');
            $user->user_role = 'customer';

            $user->save();
        }





        if (Category::count() == 0) {
            $ebook = Category::create([
                'name' => 'E-Book'
            ]);
            $cover = Category::create([
                'name' => 'Hard-Cover'
            ]);
            $paper = Category::create([
                'name' => 'Paper Back'
            ]);
            $audio = Category::create([
                'name' => 'Audio Book'
            ]);
        }


        if (Product::count() == 0) {
            $product = Product::create([
                'category_id' => $ebook->id,
                'author_id' => $user->id,
                'image' => 'https://cdn-ikpmnij.nitrocdn.com/iIBhBbmDYTHBXPSWbZZBvfUPLwofCYob/assets/images/optimized/rev-036f225/scottlmillerbooks.com/wp-content/uploads/2024/01/Interrogation-5-6-x-9-inch-600x923-1.jpg',
                'title' => 'Interrogation E-book',
                'price' => 77,
                'wittenby' => 'Scott Miller',
                'publisher' => 'Scott Publisher',
                'year' => '2012',
                'summary' => 'Lorem ipsum dolor, sit amet consectetur adipisicing elit.',
                'description' => 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Minus neque, velit doloribus, iste labore temporibus quos enim autem nostrum est ratione saepe, ipsam provident eos aliquid culpa harum error. Ipsam.',
            ]);

            $product = Product::create([
                'category_id' => $cover->id,
                'author_id' => $user->id,
                'image' => 'https://cdn-ikpmnij.nitrocdn.com/iIBhBbmDYTHBXPSWbZZBvfUPLwofCYob/assets/images/optimized/rev-036f225/scottlmillerbooks.com/wp-content/uploads/2024/03/2mvd8rg-front-shortedge-384.jpg',
                'title' => 'Interrogation E-book',
                'price' => 55,
                'disc_price' => 48,
                'wittenby' => 'Scott Miller',
                'publisher' => 'International Publisher',
                'year' => '2012',
                'summary' => 'Lorem ipsum dolor, sit amet consectetur adipisicing elit.',
                'description' => 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Minus neque, velit doloribus, iste labore temporibus quos enim autem nostrum est ratione saepe, ipsam provident eos aliquid culpa harum error. Ipsam.',
            ]);
        }

        if (Shipping::count() == 0) {
            $shipping = Shipping::create([
                'name' => 'Flat Rate',
                'amount' => 6
            ]);
        }
        
    }

}