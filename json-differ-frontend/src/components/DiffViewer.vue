<template>
  <div v-if="!diffs || diffs.length === 0" class="p-4 text-gray-500">No differences to display</div>

  <div v-else class="grid grid-cols-2 rounded-lg bg-gray-100 p-4 font-mono text-sm">
    <!-- Left Column -->
    <div class="border-r">
      <div class="mb-2 bg-gray-200 px-2 py-2 font-bold text-zinc-900">Original</div>
      <div
        v-for="(line, idx) in diffs"
        :key="`left-${idx}`"
        :class="`flex ${getBgColor(line.status, DiffSide.LEFT)}`"
      >
        <div class="w-10 pr-2 text-right text-gray-500 select-none">
          {{ line.status === DiffStatus.ADDED ? "" : (line.leftNumber ?? "") }}
        </div>
        <span v-if="line.status === DiffStatus.MODIFIED" class="flex-1 whitespace-pre-wrap">
          <span v-for="(part, partIdx) in diffChars(line.left, line.right)" :key="partIdx">
            <span v-if="part.removed" class="bg-red-500 text-white">{{ part.value }}</span>
            <span v-else-if="!part.added" class="whitespace-pre-wrap text-zinc-900">{{
              part.value
            }}</span>
          </span>
        </span>
        <div v-else-if="line.status === DiffStatus.ADDED" class="flex-1">&nbsp;</div>
        <div v-else class="flex-1 whitespace-pre-wrap text-zinc-900">
          {{ line.left }}
        </div>
      </div>
    </div>

    <!-- Right Column -->
    <div>
      <div class="mb-2 bg-gray-200 px-2 py-2 font-bold text-zinc-900">Modified</div>
      <div
        v-for="(line, idx) in diffs"
        :key="`right-${idx}`"
        :class="`flex ${getBgColor(line.status, DiffSide.RIGHT)}`"
      >
        <div class="w-10 pr-2 text-right text-gray-500 select-none">
          {{ line.rightNumber ?? "" }}
        </div>
        <span v-if="line.status === DiffStatus.MODIFIED" class="flex-1 whitespace-pre-wrap">
          <span v-for="(part, partIdx) in diffChars(line.left, line.right)" :key="partIdx">
            <span v-if="part.added" class="bg-green-400">{{ part.value }}</span>
            <span v-else-if="!part.removed" class="whitespace-pre-wrap text-zinc-900">{{
              part.value
            }}</span>
          </span>
        </span>
        <div v-else class="flex-1 whitespace-pre-wrap text-zinc-900">
          {{ line.right }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { type DiffLine, DiffSide, type DiffSide as DiffSideType, DiffStatus } from "@/types";
import { diffChars } from "@/utils/diff";

defineProps<{
  diffs: DiffLine[];
}>();

const getBgColor = (status: string, side: DiffSideType): string => {
  if (status === DiffStatus.MODIFIED) {
    return side === DiffSide.LEFT ? "bg-red-100" : "bg-green-100";
  }
  if (status === DiffStatus.ADDED && side === DiffSide.RIGHT) return "bg-green-100";
  if (status === DiffStatus.ADDED && side === DiffSide.LEFT) return "bg-zinc-300";
  if (status === DiffStatus.REMOVED && side === DiffSide.LEFT) return "bg-red-100";
  return "";
};
</script>
