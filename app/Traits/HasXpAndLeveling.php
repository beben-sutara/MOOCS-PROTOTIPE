<?php

namespace App\Traits;

use App\Models\UserXpLog;

/**
 * Trait HasXpAndLeveling
 * 
 * Menyediakan sistem XP dan leveling otomatis untuk model User.
 * 
 * Features:
 * - Otomatis menambah XP
 * - Otomatis menghitung level
 * - Track history XP
 * - Customize XP requirements per level
 */
trait HasXpAndLeveling
{
    /**
     * Configuration untuk leveling system
     * 
     * Override di User model jika ingin customize
     */
    public static function getLevelingConfig(): array
    {
        return [
            'base_xp' => 100,
            'multiplier' => 1.1,
            'max_level' => 100,
        ];
    }

    /**
     * Tambah XP ke user
     *
     * @param  int  $amount
     * @param  string  $source
     * @param  array  $metadata
     * @return array
     */
    public function addXp(int $amount, string $source = 'default', array $metadata = []): array
    {
        $this->xp = (int) ($this->xp ?? 0);
        $this->level = (int) ($this->level ?? 1);
        $this->next_level_xp = (int) ($this->next_level_xp ?? self::getLevelingConfig()['base_xp']);

        $oldLevel = $this->level;
        $oldXp = $this->xp;

        // Add XP
        $this->xp += $amount;
        $this->last_xp_earned_at = now();

        // Recalculate level
        $leveledUp = $this->recalculateLevel();

        // Save changes
        $this->save();

        // Log XP transaction
        $this->logXpTransaction($amount, $source, $metadata, $oldLevel, $oldXp, $leveledUp);

        return [
            'previous_xp' => $oldXp,
            'current_xp' => $this->xp,
            'previous_level' => $oldLevel,
            'current_level' => $this->level,
            'leveled_up' => $leveledUp,
            'next_level_xp' => $this->next_level_xp,
            'xp_progress' => $this->getXpProgress(),
        ];
    }

    /**
     * Tambah XP dari multiple sources
     *
     * @param  array  $xpArray  ['source' => amount, ...]
     * @return array
     */
    public function addMultipleXp(array $xpArray): array
    {
        $totalXp = 0;
        $results = [];

        foreach ($xpArray as $source => $amount) {
            $result = $this->addXp($amount, $source);
            $results[$source] = $result;
            $totalXp += $amount;
        }

        return [
            'total_xp_added' => $totalXp,
            'results' => $results,
            'final_level' => $this->level,
            'final_xp' => $this->xp,
        ];
    }

    /**
     * Hitung ulang level berdasarkan XP saat ini
     *
     * @return bool
     */
    private function recalculateLevel(): bool
    {
        $config = self::getLevelingConfig();
        $leveledUp = false;
        $currentLevel = 1;

        // Calculate level dari XP
        for ($level = 1; $level <= $config['max_level']; $level++) {
            $requiredXp = $this->getXpRequiredForLevel($level);
            if ($this->xp >= $requiredXp) {
                $currentLevel = $level;
            } else {
                break;
            }
        }

        // Check if leveled up
        if ($currentLevel > $this->level) {
            $leveledUp = true;
            $this->level = $currentLevel;
        } else {
            $this->level = $currentLevel;
        }

        // Update next level XP
        $this->next_level_xp = $this->getXpRequiredForLevel($this->level + 1);

        return $leveledUp;
    }

    /**
     * Get XP yang dibutuhkan untuk mencapai level tertentu
     *
     * @param  int  $level
     * @return int
     */
    public function getXpRequiredForLevel(int $level): int
    {
        $config = self::getLevelingConfig();
        
        if ($level <= 1) {
            return 0;
        }

        if ($level > $config['max_level']) {
            return PHP_INT_MAX;
        }

        $requiredXp = 0;

        for ($currentLevel = 2; $currentLevel <= $level; $currentLevel++) {
            $requiredXp += (int) round($config['base_xp'] * pow($config['multiplier'], $currentLevel - 2));
        }

        return $requiredXp;
    }

    /**
     * Get total XP needed untuk current level
     *
     * @return int
     */
    public function getTotalXpForCurrentLevel(): int
    {
        return $this->getXpRequiredForLevel($this->level);
    }

    /**
     * Get XP progress ke level berikutnya (0-100%)
     *
     * @return float
     */
    public function getXpProgress(): float
    {
        $currentLevelXp = $this->getTotalXpForCurrentLevel();
        $nextLevelXp = $this->next_level_xp;

        // XP dalam level saat ini
        $currentXpInLevel = $this->xp - $currentLevelXp;

        // Total XP yang diperlukan untuk level ini
        $totalXpNeeded = $nextLevelXp - $currentLevelXp;

        if ($totalXpNeeded <= 0) {
            return 100;
        }

        return round(($currentXpInLevel / $totalXpNeeded) * 100, 2);
    }

    /**
     * Get XP sampai level berikutnya
     *
     * @return int
     */
    public function getXpUntilNextLevel(): int
    {
        return max(0, $this->next_level_xp - $this->xp);
    }

    /**
     * Get XP dari awal level saat ini
     *
     * @return int
     */
    public function getXpInCurrentLevel(): int
    {
        return $this->xp - $this->getTotalXpForCurrentLevel();
    }

    /**
     * Check if user sudah max level
     *
     * @return bool
     */
    public function isMaxLevel(): bool
    {
        $config = self::getLevelingConfig();
        return $this->level >= $config['max_level'];
    }

    /**
     * Get rank user berdasarkan level dan XP
     *
     * @return array
     */
    public function getRank(): array
    {
        $level = (int) ($this->level ?? 1);
        $xp = (int) ($this->xp ?? 0);

        $rank = static::query()
            ->where(function ($query) use ($level, $xp) {
                $query->where('level', '>', $level)
                    ->orWhere(function ($subQuery) use ($level, $xp) {
                        $subQuery->where('level', $level)
                            ->where('xp', '>', $xp);
                    });
            })
            ->count() + 1;

        $totalUsers = static::query()->count();

        return [
            'rank' => $rank,
            'total_users' => $totalUsers,
            'percentage' => $totalUsers > 0 ? round(($rank / $totalUsers) * 100, 2) : 0,
        ];
    }

    /**
     * Log XP transaction
     *
     * @param  int  $amount
     * @param  string  $source
     * @param  array  $metadata
     * @param  int  $previousLevel
     * @param  int  $previousXp
     * @param  bool  $leveledUp
     * @return void
     */
    private function logXpTransaction(
        int $amount,
        string $source,
        array $metadata,
        int $previousLevel,
        int $previousXp,
        bool $leveledUp
    ): void {
        if (class_exists('App\Models\UserXpLog')) {
            UserXpLog::create([
                'user_id' => $this->id,
                'amount' => $amount,
                'source' => $source,
                'previous_xp' => $previousXp,
                'current_xp' => $this->xp,
                'previous_level' => $previousLevel,
                'current_level' => $this->level,
                'leveled_up' => $leveledUp,
                'metadata' => $metadata,
            ]);
        }
    }

    /**
     * Get XP summary untuk user
     *
     * @return array
     */
    public function getXpSummary(): array
    {
        $rank = $this->getRank();

        return [
            'current_xp' => $this->xp,
            'current_level' => $this->level,
            'next_level_xp' => $this->next_level_xp,
            'xp_until_next_level' => $this->getXpUntilNextLevel(),
            'xp_progress_percentage' => $this->getXpProgress(),
            'total_xp_in_current_level' => $this->getXpInCurrentLevel(),
            'is_max_level' => $this->isMaxLevel(),
            'rank' => $rank['rank'],
            'rank_percentage' => $rank['percentage'],
            'last_xp_earned_at' => $this->last_xp_earned_at,
        ];
    }

    /**
     * Reset XP dan level (untuk admin)
     *
     * @return void
     */
    public function resetXpAndLevel(): void
    {
        $config = self::getLevelingConfig();
        $this->xp = 0;
        $this->level = 1;
        $this->next_level_xp = $config['base_xp'];
        $this->last_xp_earned_at = null;
        $this->save();
    }

    /**
     * Set XP langsung (untuk admin)
     *
     * @param  int  $amount
     * @return array
     */
    public function setXp(int $amount): array
    {
        $oldXp = $this->xp;
        $oldLevel = $this->level;

        $this->xp = $amount;
        $this->recalculateLevel();
        $this->save();

        return [
            'previous_xp' => $oldXp,
            'current_xp' => $this->xp,
            'previous_level' => $oldLevel,
            'current_level' => $this->level,
        ];
    }
}
