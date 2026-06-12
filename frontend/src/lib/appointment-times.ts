/** Créneaux de 30 minutes de 00:00 à 23:30 */
export function buildHalfHourTimes(): string[] {
  const times: string[] = []
  for (let h = 0; h < 24; h++) {
    for (const m of [0, 30]) {
      times.push(`${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}`)
    }
  }
  return times
}

export const ALL_DAY_TIMES = buildHalfHourTimes()

/** Matin : 00:00 → 11:30 */
export const MORNING_TIMES = ALL_DAY_TIMES.filter((t) => {
  const [h] = t.split(':').map(Number)
  return h < 12
})

/** Après-midi & soir : 12:00 → 23:30 */
export const AFTERNOON_TIMES = ALL_DAY_TIMES.filter((t) => {
  const [h] = t.split(':').map(Number)
  return h >= 12
})
