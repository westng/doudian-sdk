#!/usr/bin/env python3
"""
Fix missing namespace declarations in SDK API files.

Usage:
  python3 scripts/fix_api_namespaces.py
  python3 scripts/fix_api_namespaces.py --dry-run --verbose
"""

from __future__ import annotations

import argparse
import re
from pathlib import Path


NAMESPACE_RE = re.compile(r"^\s*namespace\s+[^;]+;", re.MULTILINE)
DECL_RE = re.compile(
    r"^(?:abstract\s+|final\s+)?(?:class|interface|trait|enum)\s+[A-Za-z_][A-Za-z0-9_]*",
    re.MULTILINE,
)

DOUDIAN_OP_PATTERN = re.compile(
    r"(?<![\\\w])(?:DouDianOpClient|DoudianOpClient)::getInstance\(\)"
)
GLOBAL_CONFIG_PATTERN = re.compile(
    r"(?<![\\\w])(?:GlobalConfig|globalConfig)::getGlobalConfig\(\)"
)


def build_namespace(root: Path, file_path: Path) -> str:
    relative_dir = file_path.parent.relative_to(root).as_posix()
    if relative_dir == ".":
        return "DouDianSdk\\Api"
    return "DouDianSdk\\Api\\" + relative_dir.replace("/", "\\")


def insert_namespace_if_missing(code: str, namespace: str) -> tuple[str, bool]:
    if NAMESPACE_RE.search(code):
        return code, False

    ns_line = f"namespace {namespace};\n\n"
    decl_match = DECL_RE.search(code)
    if decl_match:
        pos = decl_match.start()
        return code[:pos] + ns_line + code[pos:], True

    return code.rstrip() + "\n\n" + ns_line, True


def fix_references(code: str) -> tuple[str, bool]:
    changed = False
    code, n1 = DOUDIAN_OP_PATTERN.subn(
        r"\\DouDianSdk\\Core\\Client\\DouDianOpClient::getInstance()", code
    )
    if n1 > 0:
        changed = True

    code, n2 = GLOBAL_CONFIG_PATTERN.subn(
        r"\\DouDianSdk\\Core\\Config\\GlobalConfig::getGlobalConfig()", code
    )
    if n2 > 0:
        changed = True

    return code, changed


def iter_php_files(root: Path):
    for path in sorted(root.rglob("*.php")):
        if path.is_file():
            yield path


def main() -> int:
    parser = argparse.ArgumentParser(description="Fix API namespace declarations.")
    parser.add_argument("--dry-run", action="store_true", help="Show changes without writing")
    parser.add_argument("--verbose", action="store_true", help="Print each changed file")
    args = parser.parse_args()

    root = Path("src/Api").resolve()
    if not root.exists() or not root.is_dir():
        print(f"Directory not found: {root}")
        return 1

    scanned = 0
    changed = 0

    for file_path in iter_php_files(root):
        scanned += 1
        original = file_path.read_text(encoding="utf-8", errors="ignore")
        updated = original

        namespace = build_namespace(root, file_path)
        updated, ns_changed = insert_namespace_if_missing(updated, namespace)
        updated, ref_changed = fix_references(updated)

        if updated != original:
            changed += 1
            if args.verbose:
                reasons = []
                if ns_changed:
                    reasons.append("namespace")
                if ref_changed:
                    reasons.append("refs")
                reason_str = ",".join(reasons) if reasons else "content"
                print(f"fixed: {file_path} ({reason_str})")
            if not args.dry_run:
                file_path.write_text(updated, encoding="utf-8")

    mode = "dry-run" if args.dry_run else "apply"
    print(f"done ({mode}). scanned={scanned}, changed={changed}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
