 * @copyright  Copyright (C) 2011 - 2012 Ian MacLennan, Copyright (C) 2013 - 2015 Open Source Matters, Inc. All rights reserved.
					// Binary files are presently unsupported, use this to reset the parser in the meantime
					if (strpos($line, 'Binary files') === 0)
					{
						$state = 0;
					}

						$state = 0;
						if (!file_exists(JPATH_ROOT . '/installation/index.php'))
		$rate   = $github->authorization->getRateLimit();
		// If over the API limit, we can't build this list
		if ($rate->resources->core->remaining == 0)
			throw new \RuntimeException(
				\JText::sprintf('COM_PATCHTESTER_API_LIMIT_LIST', \JFactory::getDate($rate->resources->core->reset))
			);
		}
		$pull = $github->pulls->get($this->getState()->get('github_user'), $this->getState()->get('github_repo'), $id);
		if (is_null($pull->head->repo))
		{
			throw new \RuntimeException(\JText::_('COM_PATCHTESTER_REPO_IS_GONE'));
		}
		// Set up the JHttp object
		$options = new Registry;
		$options->set('userAgent', 'JPatchTester/2.0');
		$options->set('timeout', 120);
		// Make sure we can use the cURL driver
		$driver = \JHttpFactory::getAvailableDriver($options, 'curl');

		if (!($driver instanceof \JHttpTransportCurl))
		{
			throw new \RuntimeException('Cannot use the PHP cURL adapter in this environment, cannot use patchtester', 500);
		}
		$transport = new \JHttp($options, $driver);
		$patch = $transport->get($pull->diff_url)->body;
		$files = $this->parsePatch($patch);
		if (!$files)
		{
			return false;
		}
		foreach ($files as $file)
		{
			if ($file->action == 'deleted' && !file_exists(JPATH_ROOT . '/' . $file->old))
			{
				throw new \RuntimeException(sprintf(\JText::_('COM_PATCHTESTER_FILE_DELETED_DOES_NOT_EXIST_S'), $file->old));
			if ($file->action == 'added' || $file->action == 'modified')
				// If the backup file already exists, we can't apply the patch
				if (file_exists(JPATH_COMPONENT . '/backups/' . md5($file->new) . '.txt'))
					throw new \RuntimeException(sprintf(\JText::_('COM_PATCHTESTER_CONFLICT_S'), $file->new));
				if ($file->action == 'modified' && !file_exists(JPATH_ROOT . '/' . $file->old))
					throw new \RuntimeException(sprintf(\JText::_('COM_PATCHTESTER_FILE_MODIFIED_DOES_NOT_EXIST_S'), $file->old));
				}
				$url = 'https://raw.github.com/' . urlencode($pull->head->user->login) . '/' . urlencode($pull->head->repo->name) . '/' . urlencode($pull->head->ref) . '/' . $file->new;
				$file->body = $transport->get($url)->body;
		}
		jimport('joomla.filesystem.file');
		// At this point, we have ensured that we have all the new files and there are no conflicts
		foreach ($files as $file)
		{
			// We only create a backup if the file already exists
			if ($file->action == 'deleted' || (file_exists(JPATH_ROOT . '/' . $file->new) && $file->action == 'modified'))
				if (!\JFile::copy(\JPath::clean(JPATH_ROOT . '/' . $file->old), JPATH_COMPONENT . '/backups/' . md5($file->old) . '.txt'))
					throw new \RuntimeException(
						sprintf('Can not copy file %s to %s', JPATH_ROOT . '/' . $file->old, JPATH_COMPONENT . '/backups/' . md5($file->old) . '.txt')
					);
			}
			switch ($file->action)
			{
				case 'modified':
				case 'added':
					if (!\JFile::write(\JPath::clean(JPATH_ROOT . '/' . $file->new), $file->body))
					{
						throw new \RuntimeException(sprintf('Can not write the file: %s', JPATH_ROOT . '/' . $file->new));
					}
					break;
				case 'deleted':
					if (!\JFile::delete(\JPath::clean(JPATH_ROOT . '/' . $file->old)))
					{
						throw new \RuntimeException(sprintf('Can not delete the file: %s', JPATH_ROOT . '/' . $file->old));
					}
					break;
		}
		$table                  = \JTable::getInstance('TestsTable', '\\PatchTester\\Table\\');
		$table->pull_id         = $pull->number;
		$table->data            = json_encode($files);
		$table->patched_by      = \JFactory::getUser()->id;
		$table->applied         = 1;
		$table->applied_version = JVERSION;
		if (!$table->store())
			throw new \RuntimeException($table->getError());
			throw new \RuntimeException(sprintf(\JText::_('%s - Error retrieving table data (%s)'), __METHOD__, htmlentities($table->data)));