<template>
  <div class="container mx-auto max-w-6xl px-4 py-8">
    <h1 class="mb-6 text-3xl font-bold">JSON Payload Differ</h1>

    <!-- Mode Selection Tabs -->
    <div class="mb-6 flex gap-4">
      <button
        @click="handleModeChange('example')"
        :class="[
          'rounded-lg px-6 py-3 font-medium transition-colors',
          mode === 'example'
            ? 'bg-indigo-600 text-white'
            : 'bg-gray-200 text-gray-700 hover:bg-gray-300',
        ]"
      >
        Use Example Payloads
      </button>
      <button
        @click="handleModeChange('custom')"
        :class="[
          'rounded-lg px-6 py-3 font-medium transition-colors',
          mode === 'custom'
            ? 'bg-indigo-600 text-white'
            : 'bg-gray-200 text-gray-700 hover:bg-gray-300',
        ]"
      >
        Paste Custom JSON
      </button>
    </div>

    <!-- Example Mode -->
    <div v-if="mode === 'example'" class="mb-6">
      <p class="mb-4 text-gray-600">
        This simulates receiving two webhook payloads with a 30-second delay.
      </p>
      <div class="flex gap-3">
        <button
          @click="sendExamplePayloads"
          :disabled="loading"
          class="rounded-lg bg-indigo-600 px-6 py-3 text-white transition-colors hover:bg-indigo-700 disabled:cursor-not-allowed disabled:bg-gray-400"
        >
          <div v-if="loading" class="flex items-center gap-2">
            <LoadingIcon class="size-5 text-white" />
            <span>Processing...</span>
          </div>
          <span v-else>Send Payloads & Compare</span>
        </button>
        <button
          v-if="loading"
          @click="cancelRequest"
          class="rounded-lg bg-red-500 px-6 py-3 text-white transition-colors hover:bg-red-700"
        >
          Cancel
        </button>
      </div>
    </div>

    <!-- Custom Mode -->
    <div v-if="mode === 'custom'" class="mb-6 space-y-4">
      <div>
        <label class="mb-2 block text-sm font-medium"> Payload 1 (Original JSON) </label>
        <textarea
          v-model="payload1Input"
          @input="errors.payload1 = ''"
          placeholder='{"id": 1, "name": "Product A", ...}'
          :class="[
            'h-40 w-full rounded-lg border p-3 font-mono text-sm',
            errors.payload1 ? 'border-red-500' : 'border-gray-300',
          ]"
          :disabled="loading"
        />
        <p v-if="errors.payload1" class="mt-1 text-sm text-red-600">
          {{ errors.payload1 }}
        </p>
      </div>

      <div>
        <label class="mb-2 block text-sm font-medium"> Payload 2 (Modified JSON) </label>
        <textarea
          v-model="payload2Input"
          @input="errors.payload2 = ''"
          placeholder='{"id": 1, "name": "Product B", ...}'
          :class="[
            'h-40 w-full rounded-lg border p-3 font-mono text-sm',
            errors.payload2 ? 'border-red-500' : 'border-gray-300',
          ]"
          :disabled="loading"
        />
        <p v-if="errors.payload2" class="mt-1 text-sm text-red-600">
          {{ errors.payload2 }}
        </p>
      </div>

      <div class="flex gap-3">
        <button
          @click="compareCustomPayloads"
          :disabled="loading"
          class="rounded-lg bg-indigo-600 px-6 py-3 text-white transition-colors hover:bg-indigo-700 disabled:cursor-not-allowed disabled:bg-gray-400"
        >
          <div v-if="loading" class="flex items-center gap-2">
            <LoadingIcon class="size-5 text-white" />
            <span>Comparing...</span>
          </div>
          <span v-else>Compare Payloads</span>
        </button>
        <button
          v-if="loading"
          @click="cancelRequest"
          class="rounded-lg bg-red-500 px-6 py-3 text-white transition-colors hover:bg-red-700"
        >
          Cancel
        </button>
      </div>
    </div>

    <!-- Status Messages -->
    <p
      v-if="message"
      :class="[
        'mt-4 font-medium',
        message.includes('❌')
          ? 'text-red-600'
          : message.includes('✅')
            ? 'text-green-600'
            : 'text-gray-600',
      ]"
    >
      {{ message }}
    </p>

    <!-- Progress Bar -->
    <div v-if="loading" class="mt-4 h-2 w-full rounded-full bg-gray-200">
      <div
        class="h-2 rounded-full bg-blue-600 transition-all duration-300"
        :style="{ width: `${progress}%` }"
      />
    </div>

    <!-- Results -->
    <div v-if="showResult && diffs.length > 0" class="mt-8">
      <h2 class="animate-fade-in mb-4 text-xl font-bold">Comparison Results</h2>
      <div class="animate-fade-in" style="animation-delay: 0.5s; animation-fill-mode: both">
        <DiffViewer :diffs="diffs" />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref } from "vue";

import DiffViewer from "./components/DiffViewer.vue";
import LoadingIcon from "./components/icons/LoadingIcon.vue";
import { apiClient, getErrorMessage } from "./services/api";
import type { DiffLine } from "./types";
import { validateJSON } from "./utils/validation";

const mode = ref<"example" | "custom">("example");
const loading = ref(false);
const message = ref("");
const diffs = ref<DiffLine[]>([]);
const progress = ref(0);
const showResult = ref(false);

// Custom mode states
const payload1Input = ref("");
const payload2Input = ref("");
const errors = reactive({
  payload1: "",
  payload2: "",
});

// Request cancellation
let abortController: AbortController | null = null;

const sendExamplePayloads = async () => {
  reset();
  loading.value = true;
  message.value = "Sending payload 1...";

  // Create new abort controller for this request
  abortController = new AbortController();

  try {
    await apiClient("/payload", {
      method: "POST",
      body: { type: "payload1" },
      timeout: 35000, // 35 seconds for example mode
      signal: abortController.signal,
    });

    message.value = "✅ Payload 1 sent. Waiting 30s for payload 2...";
    progress.value = 33;

    // Wait 30 seconds (allow cancellation during wait)
    await new Promise<void>((resolve, reject) => {
      const timeoutId = setTimeout(resolve, 30000);
      abortController?.signal.addEventListener("abort", () => {
        clearTimeout(timeoutId);
        reject(new Error("Request was cancelled"));
      });
    });

    message.value = "Sending payload 2...";
    await apiClient("/payload", {
      method: "POST",
      body: { type: "payload2" },
      timeout: 35000,
      signal: abortController.signal,
    });

    message.value = "✅ Payload 2 sent. Comparing...";
    progress.value = 66;

    const data = await apiClient<{ diffs: DiffLine[] }>("/compare", {
      timeout: 35000,
      signal: abortController.signal,
    });

    message.value = "✅ Comparison complete.";
    progress.value = 100;

    await new Promise((resolve) => setTimeout(resolve, 500));
    diffs.value = data.diffs || [];
    showResult.value = true;
  } catch (err: unknown) {
    const errorMessage = getErrorMessage(err);
    message.value = `❌ ${errorMessage}`;
    diffs.value = [];
  } finally {
    message.value = "";
    loading.value = false;
    abortController = null;
  }
};

const compareCustomPayloads = async () => {
  const validation1 = validateJSON(payload1Input.value);
  const validation2 = validateJSON(payload2Input.value);

  errors.payload1 = validation1.valid ? "" : validation1.error || "";
  errors.payload2 = validation2.valid ? "" : validation2.error || "";

  if (!validation1.valid || !validation2.valid) {
    return;
  }

  reset();
  loading.value = true;
  message.value = "Comparing payloads...";
  progress.value = 50;

  // Create new abort controller for this request
  abortController = new AbortController();

  try {
    const data = await apiClient<{ diffs: DiffLine[] }>("/compare-custom", {
      method: "POST",
      body: {
        payload1: validation1.data,
        payload2: validation2.data,
      },
      timeout: 10000, // 10 seconds for custom mode
      signal: abortController.signal,
    });

    message.value = "✅ Comparison complete.";
    progress.value = 100;

    await new Promise((resolve) => setTimeout(resolve, 500));
    diffs.value = data.diffs || [];
    showResult.value = true;
  } catch (err: unknown) {
    const errorMessage = getErrorMessage(err);
    message.value = `❌ ${errorMessage}`;
    diffs.value = [];
  } finally {
    message.value = "";
    loading.value = false;
    abortController = null;
  }
};

const cancelRequest = () => {
  if (abortController) {
    abortController.abort();
    message.value = "Request cancelled.";
  }
};

const reset = () => {
  // Cancel any ongoing request
  if (abortController) {
    abortController.abort();
    abortController = null;
  }

  diffs.value = [];
  message.value = "";
  loading.value = false;
  progress.value = 0;
  showResult.value = false;
  errors.payload1 = "";
  errors.payload2 = "";
};

const handleModeChange = (newMode: "example" | "custom") => {
  reset();
  mode.value = newMode;
};
</script>
