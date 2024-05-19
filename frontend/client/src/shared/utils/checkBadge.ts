export function isBadgeTaken(badgeId: number, takenBadges: Array<{ id: number }>): boolean {
console.log('takenBadges', takenBadges)
    return takenBadges.some(takenBadge => takenBadge.id === badgeId);
}
