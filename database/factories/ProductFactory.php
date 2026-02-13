<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = [
            'ชา', 'กาแฟ', 'น้ำสกัด', 'สมูทตี้', 'นมสด', 'อิตาเลียนโซดา', 'มัทฉะ', 'โฮจิฉะ', 
            'โกโก้', 'โอเลี้ยง', 'ยกล้อ', 'น้ำผลไม้', 'ชานม', 'ค็อกเทลไร้แอลกอหอล์'
        ];

        $flavors = [
            // ผลไม้
            'มังคุด', 'มะม่วง', 'ลิ้นจี่', 'พีช', 'สตรอว์เบอร์รี่', 'ส้มสายน้ำผึ้ง', 'เมล่อน', 
            'แตงโม', 'สับปะรด', 'แก้วมังกร', 'กีวี่', 'เบอร์รี่รวม', 'มะพร้าวน้ำหอม', 'ลำไย', 
            'มะขาม', 'ฝรั่ง', 'บ๊วย', 'องุ่นเคียวโฮ', 'แอปเปิ้ลเขียว', 'เสาวรส', 'มะนาวแป้น',
            // ผัก/สมุนไพร
            'ใบบัวบก', 'อัญชัน', 'คะน้าสกัดเย็น', 'ตะไคร้', 'ขิง', 'กระเจี๊ยบ', 'เก๊กฮวย', 
            'ใบเตย', 'ว่านหางจระเข้', 'แครอท', 'บีทรูท', 'เซเลอรี่', 'มะเขือเทศ',
            // อื่นๆ
            'วานิลลา', 'คาราเมล', 'ช็อกโกแลต', 'เผือก', 'มันม่วง', 'น้ำผึ้ง', 'นมข้น', 
            'คุ้กกี้แอนด์ครีม', 'สายไหม', 'กุหลาบ', 'ลาเวนเดอร์', 'พุดดิ้ง'
        ];

        $suffixes = [
            'เย็น', 'ปั่น', 'ร้อน', 'สกัดเย็น', 'พรีเมียม', 'สูตรเข้มข้น', 'ออร์แกนิก', 
            'หวานน้อย', 'วิปครีมทะลัก', 'ใส่ไข่มุก', 'พ่นไฟ', 'ท็อปปิ้งล้น', 'แยกชั้น', 
            'เชค', 'ใส่เนื้อผลไม้', 'ซิกเนเจอร์'
        ];

        // สุ่มผสมคำ (Prefix + Flavor + Suffix)
        $name = $this->faker->randomElement($categories) .
            $this->faker->randomElement($flavors) .
            $this->faker->randomElement($suffixes);

        return [
            'name' => $name,
            'price' => $this->faker->randomFloat(2, 35, 120),
            'is_active' => true,
            'description' => 'เมนู' . $name . ' รสชาติอร่อย สดชื่น ได้สุขภาพดีทุกวัน',
            'imgurl' => 'img/CV-milk-tea.png',
        ];
    }
}
