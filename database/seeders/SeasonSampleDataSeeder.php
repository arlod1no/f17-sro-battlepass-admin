<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Season;
use App\Models\BattlePass;
use App\Models\Quest;
use App\Models\Reward;
use Carbon\Carbon;

class SeasonSampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo Season mới
        $season = Season::create([
            'name' => 'Winter Legends Season',
            'description' => 'Embrace the winter spirits and conquer the frozen lands. Complete epic quests and earn legendary rewards in this seasonal adventure.',
            'start_date' => Carbon::now()->subDays(30),
            'end_date' => Carbon::now()->addDays(60),
            'is_active' => true
        ]);

        // Tạo Free Battle Pass
        $freeBattlePass = BattlePass::create([
            'name' => 'Winter Legends - Free Pass',
            'season_id' => $season->id,
            'type' => 'free',
            'is_active' => true
        ]);

        // Tạo Premium Battle Pass
        $premiumBattlePass = BattlePass::create([
            'name' => 'Winter Legends - Premium Pass',
            'season_id' => $season->id,
            'type' => 'premium',
            'is_active' => true
        ]);

        // ========================
        // DAILY QUESTS (Level 1-20)
        // ========================
        $dailyQuests = [
            [
                'name' => 'Daily Login',
                'description' => 'Log into the game for daily rewards',
                'required_action' => 'daily_login',
                'required_count' => 1,
                'exp_reward' => 100
            ],
            [
                'name' => 'Kill 50 Monsters',
                'description' => 'Defeat 50 monsters in any area',
                'required_action' => 'kill_monsters',
                'required_count' => 50,
                'exp_reward' => 200
            ],
            [
                'name' => 'Collect Gold',
                'description' => 'Collect 100,000 gold from any source',
                'required_action' => 'collect_gold',
                'required_count' => 100000,
                'exp_reward' => 150
            ],
            [
                'name' => 'Complete Dungeons',
                'description' => 'Complete 3 dungeon runs',
                'required_action' => 'complete_dungeons',
                'required_count' => 3,
                'exp_reward' => 300
            ],
            [
                'name' => 'Trade Items',
                'description' => 'Successfully trade 5 items with other players',
                'required_action' => 'trade_items',
                'required_count' => 5,
                'exp_reward' => 250
            ]
        ];

        foreach ($dailyQuests as $questData) {
            // Tạo quest cho Free Battle Pass
            $freeQuest = Quest::create([
                'battle_pass_id' => $freeBattlePass->id,
                'name' => $questData['name'],
                'description' => $questData['description'],
                'type' => 'daily',
                'required_action' => $questData['required_action'],
                'required_count' => $questData['required_count'],
                'is_active' => true
            ]);

            // Free reward
            Reward::create([
                'quest_id' => $freeQuest->id,
                'type' => 'experience',
                'name' => 'Battle Pass XP',
                'description' => 'Experience points for battle pass progression',
                'reward_points' => $questData['exp_reward'],
                'is_active' => true,
                'is_claimed' => false
            ]);

            // Tạo quest tương tự cho Premium Battle Pass
            $premiumQuest = Quest::create([
                'battle_pass_id' => $premiumBattlePass->id,
                'name' => $questData['name'] . ' (Premium)',
                'description' => $questData['description'] . ' - Premium version with bonus rewards',
                'type' => 'daily',
                'required_action' => $questData['required_action'],
                'required_count' => $questData['required_count'],
                'is_active' => true
            ]);

            // Premium reward (higher XP + bonus item)
            Reward::create([
                'quest_id' => $premiumQuest->id,
                'type' => 'experience',
                'name' => 'Premium Battle Pass XP',
                'description' => 'Enhanced experience points with premium multiplier',
                'reward_points' => $questData['exp_reward'] * 1.5,
                'is_active' => true,
                'is_claimed' => false
            ]);

            // Bonus premium reward
            Reward::create([
                'quest_id' => $premiumQuest->id,
                'type' => 'item',
                'name' => 'Premium Bonus Chest',
                'description' => 'Contains random premium items and gold',
                'reward_points' => 0,
                'reward_item' => 'PREMIUM_CHEST_DAILY',
                'is_active' => true,
                'is_claimed' => false
            ]);
        }

        // ========================
        // WEEKLY QUESTS (Level 21-50)
        // ========================
        $weeklyQuests = [
            [
                'name' => 'Weekly Warrior',
                'description' => 'Kill 500 monsters in a week',
                'required_action' => 'kill_monsters_weekly',
                'required_count' => 500,
                'exp_reward' => 1000
            ],
            [
                'name' => 'Dungeon Master',
                'description' => 'Complete 20 dungeon runs this week',
                'required_action' => 'complete_dungeons_weekly',
                'required_count' => 20,
                'exp_reward' => 1500
            ],
            [
                'name' => 'Gold Collector',
                'description' => 'Accumulate 1,000,000 gold in a week',
                'required_action' => 'collect_gold_weekly',
                'required_count' => 1000000,
                'exp_reward' => 1200
            ],
            [
                'name' => 'PvP Champion',
                'description' => 'Win 10 PvP battles this week',
                'required_action' => 'win_pvp_weekly',
                'required_count' => 10,
                'exp_reward' => 2000
            ],
            [
                'name' => 'Guild Contributor',
                'description' => 'Complete 15 guild activities',
                'required_action' => 'guild_activities_weekly',
                'required_count' => 15,
                'exp_reward' => 1800
            ]
        ];

        foreach ($weeklyQuests as $questData) {
            // Free weekly quest
            $freeQuest = Quest::create([
                'battle_pass_id' => $freeBattlePass->id,
                'name' => $questData['name'],
                'description' => $questData['description'],
                'type' => 'weekly',
                'required_action' => $questData['required_action'],
                'required_count' => $questData['required_count'],
                'is_active' => true
            ]);

            // Free weekly reward
            Reward::create([
                'quest_id' => $freeQuest->id,
                'type' => 'currency',
                'name' => 'Gold Reward',
                'description' => 'Weekly gold bonus',
                'reward_points' => $questData['exp_reward'],
                'reward_item' => 'GOLD_' . ($questData['exp_reward'] * 1000),
                'is_active' => true,
                'is_claimed' => false
            ]);

            // Premium weekly quest
            $premiumQuest = Quest::create([
                'battle_pass_id' => $premiumBattlePass->id,
                'name' => $questData['name'] . ' (Premium)',
                'description' => $questData['description'] . ' - Enhanced weekly challenge',
                'type' => 'weekly',
                'required_action' => $questData['required_action'],
                'required_count' => $questData['required_count'],
                'is_active' => true
            ]);

            // Premium weekly rewards
            Reward::create([
                'quest_id' => $premiumQuest->id,
                'type' => 'currency',
                'name' => 'Premium Gold Reward',
                'description' => 'Enhanced weekly gold bonus',
                'reward_points' => $questData['exp_reward'] * 2,
                'reward_item' => 'GOLD_' . ($questData['exp_reward'] * 2000),
                'is_active' => true,
                'is_claimed' => false
            ]);

            Reward::create([
                'quest_id' => $premiumQuest->id,
                'type' => 'item',
                'name' => 'Epic Equipment Box',
                'description' => 'Contains rare and epic equipment pieces',
                'reward_points' => 0,
                'reward_item' => 'EPIC_EQUIPMENT_BOX_WEEKLY',
                'is_active' => true,
                'is_claimed' => false
            ]);
        }

        // ========================
        // SEASONAL QUESTS (Level 51-80)
        // ========================
        $seasonalQuests = [
            [
                'name' => 'Frost Giant Slayer',
                'description' => 'Defeat the legendary Frost Giant boss 5 times',
                'required_action' => 'kill_frost_giant',
                'required_count' => 5,
                'exp_reward' => 5000
            ],
            [
                'name' => 'Winter Fortress Conqueror',
                'description' => 'Successfully complete Winter Fortress raid',
                'required_action' => 'complete_winter_fortress',
                'required_count' => 1,
                'exp_reward' => 8000
            ],
            [
                'name' => 'Ice Crystal Collector',
                'description' => 'Collect 100 rare Ice Crystals',
                'required_action' => 'collect_ice_crystals',
                'required_count' => 100,
                'exp_reward' => 6000
            ],
            [
                'name' => 'Legendary Crafter',
                'description' => 'Craft 10 legendary winter items',
                'required_action' => 'craft_legendary_winter',
                'required_count' => 10,
                'exp_reward' => 7000
            ],
            [
                'name' => 'Guild War Hero',
                'description' => 'Participate in 5 guild wars and achieve victory',
                'required_action' => 'win_guild_wars',
                'required_count' => 5,
                'exp_reward' => 10000
            ]
        ];

        foreach ($seasonalQuests as $questData) {
            // Free seasonal quest
            $freeQuest = Quest::create([
                'battle_pass_id' => $freeBattlePass->id,
                'name' => $questData['name'],
                'description' => $questData['description'],
                'type' => 'seasonal',
                'required_action' => $questData['required_action'],
                'required_count' => $questData['required_count'],
                'is_active' => true
            ]);

            // Free seasonal reward
            Reward::create([
                'quest_id' => $freeQuest->id,
                'type' => 'item',
                'name' => 'Rare Winter Item',
                'description' => 'Exclusive winter-themed equipment or consumable',
                'reward_points' => $questData['exp_reward'],
                'reward_item' => 'RARE_WINTER_ITEM_' . $freeQuest->id,
                'is_active' => true,
                'is_claimed' => false
            ]);

            // Premium seasonal quest
            $premiumQuest = Quest::create([
                'battle_pass_id' => $premiumBattlePass->id,
                'name' => $questData['name'] . ' (Premium)',
                'description' => $questData['description'] . ' - Ultimate seasonal challenge',
                'type' => 'seasonal',
                'required_action' => $questData['required_action'],
                'required_count' => $questData['required_count'],
                'is_active' => true
            ]);

            // Premium seasonal rewards (multiple high-value rewards)
            Reward::create([
                'quest_id' => $premiumQuest->id,
                'type' => 'item',
                'name' => 'Legendary Winter Artifact',
                'description' => 'Powerful legendary item with unique winter abilities',
                'reward_points' => $questData['exp_reward'] * 2,
                'reward_item' => 'LEGENDARY_WINTER_ARTIFACT_' . $premiumQuest->id,
                'is_active' => true,
                'is_claimed' => false
            ]);

            Reward::create([
                'quest_id' => $premiumQuest->id,
                'type' => 'cosmetic',
                'name' => 'Exclusive Winter Costume',
                'description' => 'Limited edition winter costume set',
                'reward_points' => 0,
                'reward_item' => 'WINTER_COSTUME_SET_' . $premiumQuest->id,
                'is_active' => true,
                'is_claimed' => false
            ]);
        }

        // ========================
        // SPECIAL MILESTONE QUESTS (Level 81-100)
        // ========================
        $milestoneQuests = [
            [
                'name' => 'Winter Legend',
                'description' => 'Complete all daily quests for 30 consecutive days',
                'required_action' => 'complete_daily_streak',
                'required_count' => 30,
                'exp_reward' => 15000,
                'level' => 85
            ],
            [
                'name' => 'Master of Winter',
                'description' => 'Reach maximum level in winter crafting skills',
                'required_action' => 'max_winter_crafting',
                'required_count' => 1,
                'exp_reward' => 20000,
                'level' => 90
            ],
            [
                'name' => 'Ultimate Champion',
                'description' => 'Win 100 PvP battles during winter season',
                'required_action' => 'win_pvp_100',
                'required_count' => 100,
                'exp_reward' => 25000,
                'level' => 95
            ],
            [
                'name' => 'Season Completionist',
                'description' => 'Complete every single quest in the winter season',
                'required_action' => 'complete_all_quests',
                'required_count' => 1,
                'exp_reward' => 50000,
                'level' => 100
            ]
        ];

        foreach ($milestoneQuests as $questData) {
            // Free milestone quest
            $freeQuest = Quest::create([
                'battle_pass_id' => $freeBattlePass->id,
                'name' => $questData['name'],
                'description' => $questData['description'],
                'type' => 'special',
                'required_action' => $questData['required_action'],
                'required_count' => $questData['required_count'],
                'is_active' => true
            ]);

            // Free milestone reward
            Reward::create([
                'quest_id' => $freeQuest->id,
                'type' => 'cosmetic',
                'name' => 'Winter Achievement Title',
                'description' => 'Exclusive achievement title: "' . $questData['name'] . '"',
                'reward_points' => $questData['exp_reward'],
                'reward_item' => 'TITLE_' . strtoupper(str_replace(' ', '_', $questData['name'])),
                'is_active' => true,
                'is_claimed' => false
            ]);

            // Premium milestone quest
            $premiumQuest = Quest::create([
                'battle_pass_id' => $premiumBattlePass->id,
                'name' => $questData['name'] . ' (Premium)',
                'description' => $questData['description'] . ' - Ultimate achievement with exclusive rewards',
                'type' => 'special',
                'required_action' => $questData['required_action'],
                'required_count' => $questData['required_count'],
                'is_active' => true
            ]);

            // Premium milestone rewards (ultimate rewards)
            Reward::create([
                'quest_id' => $premiumQuest->id,
                'type' => 'cosmetic',
                'name' => 'Mythic Winter Title',
                'description' => 'Mythic achievement title with special effects',
                'reward_points' => $questData['exp_reward'] * 2,
                'reward_item' => 'MYTHIC_TITLE_' . strtoupper(str_replace(' ', '_', $questData['name'])),
                'is_active' => true,
                'is_claimed' => false
            ]);

            Reward::create([
                'quest_id' => $premiumQuest->id,
                'type' => 'item',
                'name' => 'Mythic Winter Mount',
                'description' => 'Exclusive mythic mount with unique abilities and effects',
                'reward_points' => 0,
                'reward_item' => 'MYTHIC_WINTER_MOUNT_' . $premiumQuest->id,
                'is_active' => true,
                'is_claimed' => false
            ]);

            if ($questData['level'] == 100) {
                // Final ultimate reward for completing everything
                Reward::create([
                    'quest_id' => $premiumQuest->id,
                    'type' => 'currency',
                    'name' => 'Ultimate Season Bonus',
                    'description' => 'Massive gold and premium currency bonus',
                    'reward_points' => 100000,
                    'reward_item' => 'PREMIUM_CURRENCY_10000',
                    'is_active' => true,
                    'is_claimed' => false
                ]);
            }
        }

        $this->command->info('✅ Successfully created Winter Legends Season with:');
        $this->command->info("   - 1 Season: {$season->name}");
        $this->command->info("   - 2 Battle Passes (Free & Premium)");
        $this->command->info("   - " . Quest::where('battle_pass_id', $freeBattlePass->id)->count() . " Free quests");
        $this->command->info("   - " . Quest::where('battle_pass_id', $premiumBattlePass->id)->count() . " Premium quests");
        $this->command->info("   - " . Reward::count() . " Total rewards");
        $this->command->info('   - Quest types: Daily, Weekly, Seasonal, Special milestone');
        $this->command->info('   - Reward types: Experience, Currency, Items, Cosmetics');
    }
}
