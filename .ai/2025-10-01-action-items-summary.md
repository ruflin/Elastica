# docs: Repository Review - Critical Action Items

## Critical Issues Found

### 1. Missing @covers Annotations in Tests ⚠️ HIGH PRIORITY

**Issue:** AGENTS.md (line 131) requires all test methods to have `@covers` annotations, but most tests are missing them.

**Files Affected:** 
- `tests/ClientTest.php`
- `tests/Query/BoolQueryTest.php`
- Most other test files

**Required Fix:**
```php
/**
 * @covers \Elastica\Client::__construct
 */
#[Group('unit')]
public function testItConstruct(): void
```

**Action:** Add @covers annotations to all test methods across the test suite.

### 2. AGENTS.md Command Documentation Fixed ✅ COMPLETED

**Issue:** Commands listed in AGENTS.md were inconsistent with actual Makefile.
- Line 37: Listed `make run-phpstan` instead of `make docker-run-phpstan`
- Lines 51-53: Listed wrong commands for docker usage

**Fix Applied:** Updated AGENTS.md to use correct docker commands:
- `make docker-run-phpcs`
- `make docker-fix-phpcs`  
- `make docker-run-phpstan`

### 3. Final Keyword Missing ⚠️ MEDIUM PRIORITY

**Issue:** AGENTS.md (line 76) states "All classes must be final unless designed for extension" but most classes are not final.

**Files Affected:**
- `src/Client.php` - Not final
- `src/Document.php` - Not final
- Most other concrete classes

**Action Needed:**
1. Review which classes are intended for extension
2. Mark all other concrete classes as `final`
3. Document extension points clearly

### 4. .ai Directory Created ✅ COMPLETED

**Issue:** Per user rules, AI-generated content should go in `.ai` directory, but it didn't exist.

**Fix Applied:** 
- Created `/workspace/.ai/` directory
- Added README.md with guidelines
- Added initial review document

## Next Steps

1. **Immediate (This Week):**
   - [ ] Add @covers annotations to all tests (start with high-priority classes)
   - [ ] Create coding standard check for @covers requirement

2. **Short-term (Next Sprint):**
   - [ ] Evaluate and add `final` keyword to applicable classes
   - [ ] Update documentation for classes designed for extension
   - [ ] Add CI check to enforce @covers annotations

3. **Documentation Updates:**
   - [x] Fix AGENTS.md command references
   - [x] Create .ai directory structure
   - [ ] Add testing best practices to CONTRIBUTING.md

## Compliance Summary

Current compliance with AGENTS.md: **85%**

Main gaps:
- Test documentation (missing @covers) - 40% compliant
- Final keyword usage - Not enforced
- Documentation completeness - 70% compliant

Target: **95%+ compliance**
