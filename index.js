const twTranscriptFallback = (value, text) => {
  if (typeof value === 'string' && value.trim() !== '') {
    return value.trim()
  }
  return text
}

const twTranscriptStructureCount = (value) => {
  if (Array.isArray(value)) {
    return value.length
  }
  return 0
}

const twTranscriptToBool = (value) => {
  if (typeof value === 'boolean') {
    return value
  }

  if (typeof value === 'number') {
    return value === 1
  }

  if (typeof value === 'string') {
    const normalized = value.trim().toLowerCase()
    return ['1', 'true', 'yes', 'ja', 'on'].includes(normalized)
  }

  return false
}

const twTranscriptTimestampToSeconds = (value) => {
  if (typeof value !== 'string') {
    return 0
  }

  const timestamp = value.trim()
  if (timestamp === '') {
    return 0
  }

  const parts = timestamp.split(':').map((part) => Number.parseInt(part, 10))

  if (parts.some((part) => Number.isNaN(part) || part < 0)) {
    return 0
  }

  if (parts.length === 2) {
    return parts[0] * 60 + parts[1]
  }

  if (parts.length === 3) {
    return parts[0] * 3600 + parts[1] * 60 + parts[2]
  }

  return 0
}

const twTranscriptFormatDuration = (seconds) => {
  const total = Number.isFinite(seconds) && seconds > 0 ? Math.floor(seconds) : 0
  const minutes = Math.floor(total / 60)
  const restSeconds = total % 60

  return `${String(minutes).padStart(2, '0')}:${String(restSeconds).padStart(2, '0')}`
}

const twTranscriptEscapeHtml = (value) => {
  return String(value ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#39;')
}

const twTranscriptText = (vm, key, fallback, replacements = null) => {
  const translated = replacements ? vm?.$t?.(key, replacements) : vm?.$t?.(key)

  if (typeof translated === 'string' && translated !== '' && translated !== key) {
    return translated
  }

  if (replacements && fallback) {
    return twTranscriptInterpolate(fallback, replacements)
  }

  return fallback
}

const twTranscriptInterpolate = (template, replacements = {}) => {
  return Object.entries(replacements).reduce((result, [key, value]) => {
    return result.replaceAll(`{{ ${key} }}`, String(value))
  }, template)
}

const twTranscriptIconMarkup = (type, title, modifier) => {
  const safeTitle = twTranscriptEscapeHtml(title)
  const safeType = twTranscriptEscapeHtml(type)
  const safeModifier = twTranscriptEscapeHtml(modifier)

  return `<span class="tw-transcript-status-icon tw-transcript-status-icon--${safeModifier}" title="${safeTitle}" aria-label="${safeTitle}"><svg class="k-icon" data-type="${safeType}" aria-hidden="true"><use xlink:href="#icon-${safeType}"></use></svg></span>`
}

const twTranscriptPreviewBlock = {
  name: 'tw-transcript',
  computed: {
    kicker() {
      return twTranscriptText(this, 'tw.transcript.preview.kicker', 'Transcript')
    },
    title() {
      return twTranscriptFallback(this.content.headline, this.kicker)
    },
    count() {
      return twTranscriptStructureCount(this.content.segments)
    },
    speakersCount() {
      const segments = Array.isArray(this.content.segments) ? this.content.segments : []
      const speakers = new Set()

      for (const segment of segments) {
        const speaker = typeof segment?.speaker === 'string' ? segment.speaker.trim() : ''
        if (speaker !== '') {
          speakers.add(speaker)
        }
      }

      return speakers.size
    },
    duration() {
      const segments = Array.isArray(this.content.segments) ? this.content.segments : []
      let maxSeconds = 0

      for (const segment of segments) {
        const current = twTranscriptTimestampToSeconds(String(segment?.timestamp ?? ''))
        if (current > maxSeconds) {
          maxSeconds = current
        }
      }

      return twTranscriptFormatDuration(maxSeconds)
    },
    repeatSpeakerLabel() {
      return twTranscriptToBool(this.content.repeatspeakerpersegment)
        ? twTranscriptText(this, 'tw.transcript.preview.repeat.yes', 'Yes')
        : twTranscriptText(this, 'tw.transcript.preview.repeat.no', 'No')
    },
    segmentsLabel() {
      return twTranscriptText(this, 'tw.transcript.preview.segments', '{{ count }} segments', {
        count: this.count,
      })
    },
    speakersLabel() {
      return twTranscriptText(this, 'tw.transcript.preview.speakers', '{{ count }} speakers', {
        count: this.speakersCount,
      })
    },
    durationLabel() {
      return twTranscriptText(this, 'tw.transcript.preview.duration', 'Length: {{ duration }}', {
        duration: this.duration,
      })
    },
    repeatLabel() {
      return twTranscriptText(this, 'tw.transcript.preview.repeat', 'Repeat names: {{ value }}', {
        value: this.repeatSpeakerLabel,
      })
    },
  },
  template: `
    <div class="tw-transcript-preview">
      <p class="tw-transcript-preview__kicker">{{ kicker }}</p>
      <h3 class="tw-transcript-preview__title">{{ title }}</h3>
      <div class="tw-transcript-preview__summary-grid">
        <div class="tw-transcript-preview__summary-card">
          <p class="tw-transcript-preview__summary-value">
            <k-icon type="text" class="tw-transcript-preview__summary-icon" />
            <span>{{ segmentsLabel }}</span>
          </p>
        </div>
        <div class="tw-transcript-preview__summary-card">
          <p class="tw-transcript-preview__summary-value">
            <k-icon type="users" class="tw-transcript-preview__summary-icon" />
            <span>{{ speakersLabel }}</span>
          </p>
        </div>
        <div class="tw-transcript-preview__summary-card">
          <p class="tw-transcript-preview__summary-value">
            <k-icon type="clock" class="tw-transcript-preview__summary-icon" />
            <span>{{ durationLabel }}</span>
          </p>
        </div>
        <div class="tw-transcript-preview__summary-card">
          <p class="tw-transcript-preview__summary-value">
            <k-icon type="refresh" class="tw-transcript-preview__summary-icon" />
            <span>{{ repeatLabel }}</span>
          </p>
        </div>
      </div>
    </div>
  `,
}

const twTranscriptImporterView = {
  props: {
    tab: {
      type: String,
      default: 'importer',
    },
    tabs: {
      type: Array,
      default: () => [],
    },
  },
  data() {
    return {
      transcript: '',
      segments: [],
      loading: false,
      inserting: false,
      error: '',
      success: '',
      insertSuccess: '',
      insertError: '',
      insertPanelUrl: '',
      fileName: '',
      episodeQuery: '',
      episodes: [],
      episodesLoading: false,
      selectedEpisodeId: '',
    }
  },
  computed: {
    hasSegments() {
      return this.segments.length > 0
    },
    episodeOptions() {
      return this.groupedEpisodes.flatMap((group) =>
        group.episodes.map((episode) => ({
          value: episode.id,
          text: `${group.seasonLabel} · ${episode.displayTitle}`,
        }))
      )
    },
    previewSegments() {
      return this.segments.slice(0, 4)
    },
    groupedEpisodes() {
      const groupsMap = new Map()

      for (const episode of this.episodes) {
        const seasonNumber = Number.isInteger(episode?.seasonNumber)
          ? episode.seasonNumber
          : Number.parseInt(String(episode?.seasonNumber ?? ''), 10)
        const key = Number.isInteger(seasonNumber) && seasonNumber > 0 ? seasonNumber : 0

        if (!groupsMap.has(key)) {
          groupsMap.set(key, {
            seasonNumber: key,
            seasonLabel: key > 0 ? `Staffel ${key}` : 'Ohne Staffel',
            episodes: [],
            sortTimestamp: 0,
          })
        }

        const episodeNumber = Number.isInteger(episode?.episodeNumber)
          ? episode.episodeNumber
          : Number.parseInt(String(episode?.episodeNumber ?? ''), 10)
        let normalizedEpisodeNumber =
          Number.isInteger(episodeNumber) && episodeNumber > 0 ? episodeNumber : null

        if (normalizedEpisodeNumber === null) {
          const titleMatch = String(episode?.title ?? '').match(/(?:^|\s)E?(\d{1,4})(?:\s|$)/i)
          if (titleMatch && titleMatch[1]) {
            normalizedEpisodeNumber = Number.parseInt(titleMatch[1], 10)
          }
        }

        if (normalizedEpisodeNumber === null) {
          const slugMatch = String(episode?.id ?? '').match(/tw(\d{1,4})/i)
          if (slugMatch && slugMatch[1]) {
            normalizedEpisodeNumber = Number.parseInt(slugMatch[1], 10)
          }
        }

        const episodePrefix =
          Number.isInteger(normalizedEpisodeNumber) && normalizedEpisodeNumber > 0
            ? `E${normalizedEpisodeNumber} `
            : ''

        groupsMap.get(key).episodes.push({
          ...episode,
          displayTitle: `${episodePrefix}${episode.title}`,
        })

        const currentGroup = groupsMap.get(key)
        const timestamp = Number.parseInt(String(episode?.sortTimestamp ?? '0'), 10)
        if (Number.isInteger(timestamp) && timestamp > currentGroup.sortTimestamp) {
          currentGroup.sortTimestamp = timestamp
        }
      }

      return Array.from(groupsMap.values())
        .map((group) => ({
          ...group,
          episodes: [...group.episodes].sort((a, b) => {
            const timestampA = Number.parseInt(String(a?.sortTimestamp ?? '0'), 10)
            const timestampB = Number.parseInt(String(b?.sortTimestamp ?? '0'), 10)

            if (timestampA === timestampB) {
              return String(a?.id ?? '').localeCompare(String(b?.id ?? ''))
            }

            return timestampB - timestampA
          }),
        }))
        .sort((a, b) => {
          if (a.sortTimestamp === b.sortTimestamp) {
            if (a.seasonNumber === 0 && b.seasonNumber !== 0) {
              return 1
            }

            if (a.seasonNumber !== 0 && b.seasonNumber === 0) {
              return -1
            }

            return b.seasonNumber - a.seasonNumber
          }

          return b.sortTimestamp - a.sortTimestamp
        })
    },
  },
  methods: {
    clearState() {
      this.error = ''
      this.success = ''
      this.insertSuccess = ''
      this.insertError = ''
      this.insertPanelUrl = ''
    },
    clearAll() {
      this.transcript = ''
      this.segments = []
      this.episodes = []
      this.selectedEpisodeId = ''
      this.episodeQuery = ''
      this.fileName = ''
      this.clearState()
    },
    async loadTranscriptFile(file) {
      this.clearState()

      if (!file) {
        return
      }

      try {
        this.transcript = await file.text()
        this.fileName = typeof file.name === 'string' ? file.name : ''
      } catch (_error) {
        this.fileName = ''
        this.error = twTranscriptText(this, 'tw.transcript.error.readFile', 'Could not read file.')
      }
    },
    async parseTranscript() {
      this.clearState()

      if (this.transcript.trim() === '') {
        this.error = twTranscriptText(
          this,
          'tw.transcript.error.noTranscript',
          'Please paste a transcript or load a file first.'
        )
        return
      }

      this.loading = true

      try {
        const result = await this.$api.post('tw-transcript/import', {
          transcript: this.transcript,
        })

        if (!result || result.status !== 'ok') {
          throw new Error(
            result?.message ||
              twTranscriptText(this, 'tw.transcript.error.importFailed', 'Import failed.')
          )
        }

        this.segments = Array.isArray(result.segments) ? result.segments : []
        this.success = twTranscriptText(
          this,
          'tw.transcript.success.detected',
          '{{ count }} segments detected.',
          { count: this.segments.length }
        )
        await this.searchEpisodes()
      } catch (error) {
        this.error =
          error?.message ||
          twTranscriptText(this, 'tw.transcript.error.importFailed', 'Import failed.')
        this.segments = []
      } finally {
        this.loading = false
      }
    },
    async onFileChange(event) {
      const file = event?.target?.files?.[0]
      await this.loadTranscriptFile(file)

      event.target.value = ''
    },
    async onDropFiles(files) {
      const file = files?.[0] ?? null
      await this.loadTranscriptFile(file)
    },
    pickTranscriptFile() {
      const input = document.createElement('input')
      input.type = 'file'
      input.accept = '.txt,.json,text/plain,application/json'
      input.addEventListener(
        'change',
        async (event) => {
          await this.onFileChange(event)
        },
        { once: true }
      )
      input.click()
    },
    async searchEpisodes() {
      this.episodesLoading = true
      try {
        const result = await this.$api.get(
          `tw-transcript/episodes?q=${encodeURIComponent(this.episodeQuery)}`
        )
        this.episodes = Array.isArray(result?.episodes) ? result.episodes : []
        if (this.episodeQuery.trim() !== '' && this.episodes.length > 0) {
          this.selectedEpisodeId = this.episodes[0].id
        } else if (this.episodes.length === 0) {
          this.selectedEpisodeId = ''
        }
      } catch (_err) {
        this.episodes = []
        this.selectedEpisodeId = ''
      } finally {
        this.episodesLoading = false
      }
    },
    async insertBlock() {
      this.insertError = ''
      this.insertSuccess = ''

      if (!this.selectedEpisodeId) {
        this.insertError = twTranscriptText(
          this,
          'tw.transcript.error.selectEpisode',
          'Please select an episode.'
        )
        return
      }

      if (this.segments.length === 0) {
        this.insertError = twTranscriptText(
          this,
          'tw.transcript.error.noSegments',
          'No segments available to insert.'
        )
        return
      }

      this.inserting = true

      try {
        const result = await this.$api.post('tw-transcript/insert', {
          pageId: this.selectedEpisodeId,
          segments: this.segments,
        })

        if (!result || result.status !== 'ok') {
          throw new Error(
            result?.message ||
              twTranscriptText(this, 'tw.transcript.error.insertFailed', 'Insert failed.')
          )
        }

        this.insertSuccess = twTranscriptText(
          this,
          'tw.transcript.success.inserted',
          'Block inserted into the episode successfully.'
        )
        this.insertPanelUrl = typeof result?.panelUrl === 'string' ? result.panelUrl : ''
        this.segments = []
        this.episodes = []
        this.selectedEpisodeId = ''
        this.episodeQuery = ''
        this.fileName = ''
        this.transcript = ''
        this.success = ''
        this.insertError = ''
      } catch (error) {
        this.insertError =
          error?.message ||
          twTranscriptText(this, 'tw.transcript.error.insertFailed', 'Insert failed.')
      } finally {
        this.inserting = false
      }
    },
  },
  template: `
    <k-panel-inside class="tw-transcript-importer-view">
      <k-view>
        <k-header>{{ $t('tw.transcript.area.title') }}</k-header>
        <k-tabs :tab="tab" :tabs="tabs" />

        <k-grid variant="fields" class="k-sections">
          <k-column width="1/2">
            <k-section :headline="$t('tw.transcript.section.importFile')">
              <k-box theme="info" class="tw-transcript-message-box">
                {{ $t('tw.transcript.info.importer') }}
              </k-box>

              <k-field>
                <k-dropzone @drop="onDropFiles">
                  <k-empty
                    class="tw-transcript-dropzone"
                    icon="upload"
                    layout="cards"
                    @click="pickTranscriptFile"
                  >
                    {{ fileName || $t('tw.transcript.placeholder.file') }}
                  </k-empty>
                </k-dropzone>
              </k-field>

              <k-box v-if="fileName" theme="info" class="tw-transcript-message-box">
                {{ $t('tw.transcript.message.fileLoaded', { fileName }) }}
              </k-box>
            </k-section>

            <k-section :headline="$t('tw.transcript.section.transcript')">
              <k-field class="tw-transcript-importer-textarea" input="tw-transcript-importer-textarea-input">
                <div class="k-input" data-type="textarea">
                  <div class="k-input-element">
                    <div class="k-textarea-input" data-size="medium">
                      <div class="k-textarea-input-wrapper">
                        <textarea
                          id="tw-transcript-importer-textarea-input"
                          class="k-textarea-input-native"
                          data-font="monospace"
                          spellcheck="false"
                          :placeholder="$t('tw.transcript.placeholder.transcript')"
                          :value="transcript"
                          @input="transcript = $event.target.value"
                        ></textarea>
                      </div>
                    </div>
                  </div>
                </div>
              </k-field>

              <k-button-group class="tw-transcript-actions">
                <k-button icon="check" variant="filled" :disabled="loading" @click="parseTranscript">
                  {{ loading ? $t('tw.transcript.action.checking') : $t('tw.transcript.action.generatePreview') }}
                </k-button>
                <k-button icon="refresh" variant="filled" :disabled="loading" @click="clearAll">
                  {{ $t('tw.transcript.action.reset') }}
                </k-button>
              </k-button-group>
            </k-section>
          </k-column>

          <k-column width="1/2">
            <k-section :headline="$t('tw.transcript.section.preview')">
              <k-box v-if="insertSuccess" theme="positive" class="tw-transcript-message-box">
                {{ insertSuccess }}
              </k-box>

              <k-box v-if="error" theme="negative" class="tw-transcript-message-box">
                {{ error }}
              </k-box>

              <k-box v-if="success" theme="positive" class="tw-transcript-message-box">
                {{ success }}
              </k-box>

              <k-empty v-if="hasSegments === false" icon="text">
                {{ $t('tw.transcript.message.previewEmpty') }}
              </k-empty>

              <ol v-else class="tw-transcript-preview-list">
                <li v-for="(segment, index) in previewSegments" :key="index" class="tw-transcript-preview-list__item">
                  <div class="tw-transcript-preview-list__meta">
                    <strong>{{ segment.speaker || $t('tw.transcript.speaker.unknown') }}</strong>
                    <span v-if="segment.timestamp">{{ segment.timestamp }}</span>
                  </div>
                  <p class="tw-transcript-preview-list__text">{{ segment.text }}</p>
                </li>
              </ol>

              <p v-if="segments.length > previewSegments.length" class="tw-transcript-preview-list__more">
                {{ $t('tw.transcript.message.previewMore', { count: segments.length - previewSegments.length }) }}
              </p>
            </k-section>

            <k-section v-if="hasSegments" :headline="$t('tw.transcript.section.insert')">
              <k-field :label="$t('tw.transcript.field.episodeSearch')">
                <k-input
                  type="text"
                  icon="search"
                  :value="episodeQuery"
                  :placeholder="$t('tw.transcript.placeholder.search')"
                  @input="episodeQuery = $event; searchEpisodes()"
                />
              </k-field>

              <k-box v-if="episodesLoading" theme="info" class="tw-transcript-message-box">
                {{ $t('tw.transcript.message.searching') }}
              </k-box>

              <k-select-field
                v-else
                :label="$t('tw.transcript.field.episode')"
                :options="episodeOptions"
                :value="selectedEpisodeId"
                :empty="$t('tw.transcript.message.overviewEmpty')"
                @input="selectedEpisodeId = $event"
              />

              <k-box v-if="insertError" theme="negative" class="tw-transcript-message-box">
                {{ insertError }}
              </k-box>

              <k-button-group class="tw-transcript-actions">
                <k-button
                  icon="add"
                  variant="filled"
                  :disabled="inserting || !selectedEpisodeId"
                  @click="insertBlock"
                >
                  {{ inserting ? $t('tw.transcript.action.inserting') : $t('tw.transcript.action.insert') }}
                </k-button>

                <k-button
                  v-if="insertPanelUrl"
                  icon="open"
                  variant="filled"
                  @click="$go(insertPanelUrl)"
                >
                  {{ $t('tw.transcript.action.openEpisode') }}
                </k-button>
              </k-button-group>
            </k-section>
          </k-column>
        </k-grid>
      </k-view>
    </k-panel-inside>
  `,
}

const twTranscriptOverviewView = {
  props: {
    tab: {
      type: String,
      default: 'overview',
    },
    tabs: {
      type: Array,
      default: () => [],
    },
  },
  data() {
    return {
      loading: false,
      query: '',
      episodes: [],
      error: '',
    }
  },
  computed: {
    overviewColumns() {
      return {
        episode: {
          label: twTranscriptText(this, 'tw.transcript.table.episode', 'Episode'),
          type: 'text',
          width: '6rem',
        },
        name: {
          label: twTranscriptText(this, 'tw.transcript.table.name', 'Name'),
          type: 'html',
        },
        slug: {
          label: twTranscriptText(this, 'tw.transcript.table.slug', 'Slug'),
          type: 'text',
          width: '16rem',
        },
        status: {
          label: twTranscriptText(this, 'tw.transcript.table.status', 'Transcript'),
          type: 'html',
          width: '5rem',
          align: 'center',
        },
      }
    },
    overviewRows() {
      return this.episodes.map((episode) => {
        const episodeNumber = Number.isInteger(episode?.episodeNumber)
          ? `E${episode.episodeNumber}`
          : twTranscriptText(this, 'tw.transcript.table.noEpisode', 'No number')
        const title = twTranscriptEscapeHtml(episode?.title)
        const panelUrl = twTranscriptEscapeHtml(episode?.panelUrl)
        const slug = twTranscriptEscapeHtml(episode?.slug)
        const statusLabel = episode?.hasTranscriptBlock
          ? twTranscriptText(this, 'tw.transcript.status.available', 'Transcript available')
          : twTranscriptText(this, 'tw.transcript.status.missing', 'Transcript missing')

        return {
          episode: episodeNumber,
          name: `<a class="tw-transcript-overview-cell__title" href="${panelUrl}">${title}</a>`,
          slug,
          status: episode?.hasTranscriptBlock
            ? twTranscriptIconMarkup('check', statusLabel, 'available')
            : twTranscriptIconMarkup('cancel', statusLabel, 'missing'),
        }
      })
    },
  },
  mounted() {
    this.loadOverview()
  },
  methods: {
    async loadOverview() {
      this.loading = true
      this.error = ''
      try {
        const result = await this.$api.get(
          `tw-transcript/overview?q=${encodeURIComponent(this.query.trim())}`
        )
        this.episodes = Array.isArray(result?.episodes) ? result.episodes : []
      } catch (error) {
        this.error = error?.message || 'Overview konnte nicht geladen werden.'
        this.episodes = []
      } finally {
        this.loading = false
      }
    },
  },
  template: `
    <k-panel-inside class="tw-transcript-overview-view">
      <k-view>
        <k-header>{{ $t('tw.transcript.area.title') }}</k-header>
        <k-tabs :tab="tab" :tabs="tabs" />

        <k-section :headline="$t('tw.transcript.section.overview')">
          <k-field :label="$t('tw.transcript.field.episodeSearch')">
            <k-input
              type="search"
              icon="search"
              :value="query"
              :placeholder="$t('tw.transcript.placeholder.search')"
              @input="query = $event; loadOverview()"
            />
          </k-field>

          <k-box v-if="error" theme="negative" class="tw-transcript-message-box">
            {{ error }}
          </k-box>

          <k-box v-if="loading" theme="info" class="tw-transcript-message-box">
            {{ $t('tw.transcript.message.overviewLoading') }}
          </k-box>

          <k-table
            v-else
            :columns="overviewColumns"
            :rows="overviewRows"
            :empty="$t('tw.transcript.message.overviewEmpty')"
            :index="false"
          />
        </k-section>
      </k-view>
    </k-panel-inside>
  `,
}

const twTranscriptSettingsView = {
  props: {
    tab: {
      type: String,
      default: 'settings',
    },
    tabs: {
      type: Array,
      default: () => [],
    },
  },
  template: `
    <k-panel-inside class="tw-transcript-settings-view">
      <k-view>
        <k-header>{{ $t('tw.transcript.area.title') }}</k-header>
        <k-tabs :tab="tab" :tabs="tabs" />

        <k-section :headline="$t('tw.transcript.section.settings')">
          <k-box theme="info">
            {{ $t('tw.transcript.message.settingsPending') }}
          </k-box>
        </k-section>
      </k-view>
    </k-panel-inside>
  `,
}

panel.plugin('tw/transcript', {
  blocks: {
    'tw-transcript': twTranscriptPreviewBlock,
  },
  components: {
    'k-tw-transcript-importer-view': twTranscriptImporterView,
    'k-tw-transcript-overview-view': twTranscriptOverviewView,
    'k-tw-transcript-settings-view': twTranscriptSettingsView,
  },
})
