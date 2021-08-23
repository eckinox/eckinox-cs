if [ -z "$GITHUB_SHA" ]
then
	against=""
else
	against="$GITHUB_SHA"
fi

IFS='
'
CHANGED_FILES=$(git diff --name-only --diff-filter=ACMRTUXBd ${against})
if ! echo "${CHANGED_FILES}" | grep -qE "^(\\.php_cs(\\.dist)?|composer\\.lock)$"; then EXTRA_ARGS=$(printf -- '--path-mode=intersection\n--\n%s' "${CHANGED_FILES}"); else EXTRA_ARGS=''; fi

vendor/bin/php-cs-fixer fix --config=.php_cs.dist -vvv --dry-run --stop-on-violation --using-cache=no ${EXTRA_ARGS}

if [ $? -ne 0 ]; then
		echo "\nRun the following command to attempt automatic fix:"
		echo "vendor/bin/php-cs-fixer fix --config=.php_cs.dist -vvv"
		exit 1
fi
