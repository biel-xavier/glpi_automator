import React, { useState, useEffect } from 'react';
import {
  Dialog, DialogTitle, DialogContent, DialogActions,
  Button, TextField, Select, MenuItem, FormControl,
  InputLabel, Box, Typography, IconButton, Paper,
  Switch, FormControlLabel
} from '@mui/material';
import { Add as AddIcon, Delete as DeleteIcon } from '@mui/icons-material';

const getInitialFormData = () => ({
  id: null,
  name: '',
  itemtype: '',
  is_active: 1,
  actions: []
});

const parseConfiguration = (configuration) => {
  if (typeof configuration !== 'string') {
    return { ...(configuration || {}) };
  }

  try {
    return JSON.parse(configuration || '{}');
  } catch (error) {
    return {};
  }
};

const normalizeRule = (rule) => ({
  ...getInitialFormData(),
  ...rule,
  actions: Array.isArray(rule?.actions)
    ? rule.actions.map((action) => ({
        ...action,
        configuration: parseConfiguration(action.configuration)
      }))
    : []
});

export default function RuleEditor({ open, onClose, rule, onSave }) {
  const [formData, setFormData] = useState(getInitialFormData);
  const [itemTypes, setItemTypes] = useState([]);
  const [fields, setFields] = useState([]);

  useEffect(() => {
    fetchItemTypes();
  }, []);

  useEffect(() => {
    if (!open) {
      return;
    }

    if (rule) {
      const normalizedRule = normalizeRule(rule);
      setFormData(normalizedRule);
      if (normalizedRule.itemtype) {
        fetchFields(normalizedRule.itemtype);
      } else {
        setFields([]);
      }
      return;
    }

    setFormData(getInitialFormData());
    setFields([]);
  }, [open, rule]);

  const fetchItemTypes = async () => {
    try {
      const response = await fetch('../front/api.php?action=get_itemtypes');
      const data = await response.json();
      if (data.types) {
        setItemTypes(data.types);
      }
    } catch (error) {
      console.error("Failed to fetch item types", error);
    }
  };

  const fetchFields = async (itemtype) => {
    try {
      const response = await fetch(`../front/api.php?action=get_fields&itemtype=${itemtype}`);
      const data = await response.json();
      if (data.fields) {
        setFields(data.fields);
      }
    } catch (error) {
      console.error("Failed to fetch fields", error);
    }
  };

  const handleItemTypeChange = (value) => {
    setFormData({ ...formData, itemtype: value });
    fetchFields(value);
  };

  const addAction = () => {
    setFormData({
      ...formData,
      actions: [
        ...formData.actions,
        { action_type: 'AUTO_INCREMENT', configuration: { field: '' } }
      ]
    });
  };

  const removeAction = (index) => {
    const newActions = formData.actions.filter((_, i) => i !== index);
    setFormData({ ...formData, actions: newActions });
  };

  const updateAction = (index, field, value) => {
    const newActions = [...formData.actions];
    if (typeof newActions[index].configuration === 'string') {
      try {
        newActions[index].configuration = JSON.parse(newActions[index].configuration);
      } catch (e) {
        newActions[index].configuration = {};
      }
    }
    newActions[index].configuration[field] = value;
    setFormData({ ...formData, actions: newActions });
  };

  const handleSave = async () => {
    try {
      const body = new URLSearchParams();
      body.append('_glpi_csrf_token', window.glpi_csrf_token || '');
      body.append('data', JSON.stringify(formData));

      const response = await fetch('../front/api.php?action=save_rule', {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'X-Glpi-Csrf-Token': window.glpi_csrf_token || ''
        },
        body: body
      });
      const data = await response.json();
      if (data.success) {
        onSave();
        onClose();
      }
    } catch (error) {
      console.error("Failed to save rule", error);
    }
  };

  return (
    <Dialog open={open} onClose={onClose} maxWidth="md" fullWidth>
      <DialogTitle>{rule ? 'Edit Rule' : 'New Rule'}</DialogTitle>
      <DialogContent>
        <Box sx={{ display: 'flex', flexDirection: 'column', gap: 2, mt: 2 }}>
          <TextField
            label="Rule Name"
            value={formData.name}
            onChange={(e) => setFormData({ ...formData, name: e.target.value })}
            fullWidth
          />

          <FormControl fullWidth>
            <InputLabel>Item Type</InputLabel>
            <Select
              value={formData.itemtype}
              label="Item Type"
              onChange={(e) => handleItemTypeChange(e.target.value)}
            >
              {itemTypes.map((type) => (
                <MenuItem key={type} value={type}>{type}</MenuItem>
              ))}
            </Select>
          </FormControl>

          <FormControlLabel
            control={
              <Switch
                checked={formData.is_active == 1}
                onChange={(e) => setFormData({ ...formData, is_active: e.target.checked ? 1 : 0 })}
              />
            }
            label="Active"
          />

          <Typography variant="h6" sx={{ mt: 2 }}>Actions</Typography>

          {formData.actions.map((action, index) => (
            <Paper key={index} sx={{ p: 2, bgcolor: 'grey.50' }}>
              <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 2 }}>
                <Typography variant="subtitle1">Auto Increment</Typography>
                <IconButton size="small" color="error" onClick={() => removeAction(index)}>
                  <DeleteIcon />
                </IconButton>
              </Box>
              <FormControl fullWidth>
                <InputLabel>Target Field</InputLabel>
                <Select
                  value={action.configuration.field ? `${action.configuration.table}.${action.configuration.field}` : ''}
                  label="Target Field"
                  onChange={(e) => {
                    const [table, field] = e.target.value.split('.');
                    updateAction(index, 'table', table);
                    updateAction(index, 'field', field);
                  }}
                >
                  {fields.map((f, i) => {
                    if (typeof f.label !== 'string') {
                      console.warn("Invalid field object at index " + i, f);
                      return null;
                    }
                    return (
                      <MenuItem key={`${f.table}.${f.field}`} value={`${f.table}.${f.field}`}>
                        {f.label}
                      </MenuItem>
                    );
                  })}
                </Select>
              </FormControl>
            </Paper>
          ))}

          <Button
            startIcon={<AddIcon />}
            onClick={addAction}
            variant="outlined"
            disabled={!formData.itemtype}
          >
            Add Action
          </Button>
        </Box>
      </DialogContent>
      <DialogActions>
        <Button onClick={onClose}>Cancel</Button>
        <Button onClick={handleSave} variant="contained">Save</Button>
      </DialogActions>
    </Dialog>
  );
}
