"use client";

import { useState } from "react";

import DiffViewer from "@/components/DiffViewer/DiffViewer";
import { LoadingIcon } from "@/components/Icons";
import { DiffLine } from "@/lib/types";

export default function Home() {
  const [loading, setLoading] = useState(false);
  const [message, setMessage] = useState("");
  const [diffs, setDiffs] = useState<DiffLine[]>([]);
  const [progress, setProgress] = useState(0);
  const [showResult, setShowResult] = useState(false);

  const sendPayloads = async () => {
    reset();
    setLoading(true);
    setMessage("🚀 Sending payload 1...");
    try {
      // send payload 1
      const res1 = await fetch("/api/payload", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ type: "payload1" }),
      });
      if (!res1.ok) {
        const error = await res1.json();
        throw new Error(error.error || "Failed sending payload 1");
      }
      setMessage("✅ Payload 1 sent. Waiting 30s for payload 2...");
      setProgress(33);

      // wait 30s
      await new Promise((resolve) => setTimeout(resolve, 3000));
      setMessage("🚀 Sending payload 2...");
      const res2 = await fetch("/api/payload", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ type: "payload2" }),
      });
      if (!res2.ok) {
        const error = await res2.json();
        throw new Error(error.error || "Failed sending payload 2");
      }
      setMessage("✅ Payload 2 sent. Be patient for comparing...");
      setProgress(66);

      // request comparison
      const compareRes = await fetch("/api/compare");
      if (!compareRes.ok) {
        const error = await compareRes.json();
        throw new Error(error.error || "Comparison failed");
      }

      const data = await compareRes.json();
      setMessage("✅ Comparison complete.");
      setProgress(100);

      setDiffs(data.diffs || []);

      await new Promise((resolve) => setTimeout(resolve, 1000));
      setShowResult(true);
    } catch (err: any) {
      setMessage("❌ Error: " + err.message);
      setDiffs([]);
    } finally {
      setMessage("");
      setLoading(false);
    }
  };

  const reset = () => {
    setDiffs([]);
    setMessage("");
    setLoading(false);
    setProgress(0);
  };

  return (
    <div className="container mx-auto px-4 py-8">
      <button
        onClick={sendPayloads}
        disabled={loading}
        className="cursor-pointer rounded bg-indigo-600 px-4 py-2 text-white transition-colors hover:bg-indigo-700 disabled:bg-gray-400"
      >
        {loading ? (
          <div className="flex items-center gap-2">
            <LoadingIcon className="size-5 text-white" />
            <span>Processing...</span>
          </div>
        ) : (
          "Send Payloads & Compare"
        )}
      </button>

      {message && <p className="mt-3">{message}</p>}
      {loading && (
        <div className="mt-3 h-2 w-full rounded-full bg-gray-200">
          <div
            className="h-2 rounded-full bg-blue-600 transition-all duration-300"
            style={{ width: `${progress}%` }}
          />
        </div>
      )}

      {showResult && diffs && diffs.length > 0 && (
        <div className="mt-8">
          <h2 className="animate-fade-in mb-4 text-xl font-bold">
            Comparison Results
          </h2>
          <div
            className="animate-fade-in"
            style={{ animationDelay: "0.5s", animationFillMode: "both" }}
          >
            <DiffViewer diffs={diffs} />
          </div>
        </div>
      )}
    </div>
  );
}
